<?php
declare(strict_types=1);

$databaseUrl = getenv('DATABASE_URL');
if (!is_string($databaseUrl) || trim($databaseUrl) === '') {
    echo "SKIP: DATABASE_URL não definida; fluxo PostgreSQL não executado.\n";
    exit(0);
}

require_once dirname(__DIR__) . '/backend/services/AuthService.php';

use FixIT\Services\AuthService;

$application = require dirname(__DIR__) . '/backend/bootstrap.php';
$pdo = $application['pdo'];
$users = $application['users'];
$categories = $application['categories'];
$administration = $application['administration_controller'];
$tickets = $application['technician_ticket_controller'];
$ticketService = $application['ticket_service'];

function postgresqlFlowAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

postgresqlFlowAssert($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql', 'fluxo precisa usar PDO PostgreSQL');
$adminRow = $users->findByEmail('admin@fixit.test');
$ownerRow = $users->findByEmail('mariana@fixit.test');
$technicianRow = $users->findByEmail('douglas@fixit.test');
postgresqlFlowAssert($adminRow !== null && $ownerRow !== null && $technicianRow !== null, 'seed precisa fornecer os três perfis');
$session = [];
$auth = new AuthService($users, $session, static function (): void {});
$authenticatedAdmin = $auth->login('admin@fixit.test', 'FixIT@Admin2026');
postgresqlFlowAssert($authenticatedAdmin !== null && $authenticatedAdmin['perfil'] === 'administrador', 'administrador inicial precisa autenticar no PostgreSQL');
$admin = ['id' => (int) $adminRow['id'], 'nome' => (string) $adminRow['nome'], 'perfil' => (string) $adminRow['perfil']];
$owner = ['id' => (int) $ownerRow['id'], 'nome' => (string) $ownerRow['nome'], 'perfil' => (string) $ownerRow['perfil']];
$technician = ['id' => (int) $technicianRow['id'], 'nome' => (string) $technicianRow['nome'], 'perfil' => (string) $technicianRow['perfil']];
$activeCategories = $categories->list(true);
postgresqlFlowAssert($activeCategories !== [], 'seed precisa fornecer categoria ativa');
$beforeTickets = (int) $pdo->query('SELECT COUNT(*) FROM chamados')->fetchColumn();

$pdo->beginTransaction();
try {
    $temporaryUser = $administration->saveUser($admin, null, ['name' => 'Usuário PostgreSQL', 'email' => 'postgresql.temp@fixit.test', 'role' => 'colaborador', 'status' => 'ativo', 'password' => 'temporaria123']);
    postgresqlFlowAssert($temporaryUser > 0, 'INSERT PostgreSQL precisa retornar ID de usuário');
    $number = $ticketService->open($owner, ['titulo' => 'Teste PostgreSQL', 'descricao' => 'Validação transacional do fluxo completo no PostgreSQL.', 'categoria_id' => (int) $activeCategories[0]['id'], 'prioridade' => 'media']);
    postgresqlFlowAssert($number > 0, 'INSERT PostgreSQL precisa retornar número do chamado');
    $tickets->assign($technician, $number);
    $tickets->update($technician, $number, 'Atualização registrada no PostgreSQL.');
    $tickets->close($technician, $number, 'Fluxo PostgreSQL concluído e validado com sucesso.');
    $detail = $tickets->detail($admin, $number);
    postgresqlFlowAssert($detail !== null && $detail['status'] === 'encerrado' && count($detail['history']) === 4, 'fluxo e histórico precisam concluir no PostgreSQL');
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

postgresqlFlowAssert((int) $pdo->query('SELECT COUNT(*) FROM chamados')->fetchColumn() === $beforeTickets, 'teste PostgreSQL não pode deixar dados residuais');
echo "OK: autenticação de perfis, administração e chamados validados no PostgreSQL sem resíduos.\n";
