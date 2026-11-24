<?php
declare(strict_types=1);

$application = require dirname(__DIR__) . '/backend/bootstrap.php';
$pdo = $application['pdo'];
$users = $application['users'];
$categories = $application['categories'];
$service = $application['ticket_service'];
$collaboratorController = $application['collaborator_ticket_controller'];
$technicianController = $application['technician_ticket_controller'];
$administrationController = $application['administration_controller'];

function searchSummaryAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$ownerRow = $users->findByEmail('mariana@fixit.test');
$technicianRow = $users->findByEmail('douglas@fixit.test');
$adminRow = $users->findByEmail('admin@fixit.test');
$categoryRows = $categories->list(true);
searchSummaryAssert($ownerRow !== null && $technicianRow !== null && $adminRow !== null && $categoryRows !== [], 'seed precisa fornecer perfis e categoria');
$owner = ['id' => (int) $ownerRow['id'], 'nome' => (string) $ownerRow['nome'], 'perfil' => (string) $ownerRow['perfil']];
$technician = ['id' => (int) $technicianRow['id'], 'nome' => (string) $technicianRow['nome'], 'perfil' => (string) $technicianRow['perfil']];
$administrator = ['id' => (int) $adminRow['id'], 'nome' => (string) $adminRow['nome'], 'perfil' => (string) $adminRow['perfil']];
$before = $administrationController->summary($administrator);

$pdo->beginTransaction();
try {
    $openNumber = $service->open($owner, ['titulo' => 'Filtro alfa temporário', 'descricao' => 'Primeiro chamado para testar pesquisa persistida.', 'categoria_id' => (int) $categoryRows[0]['id'], 'prioridade' => 'baixa']);
    $serviceNumber = $service->open($owner, ['titulo' => 'Filtro beta temporário', 'descricao' => 'Segundo chamado para testar pesquisa persistida.', 'categoria_id' => (int) $categoryRows[0]['id'], 'prioridade' => 'alta']);
    $technicianController->assign($technician, $serviceNumber);

    searchSummaryAssert(count($collaboratorController->list($owner, ['busca' => 'ALFA', 'status' => 'aberto'])) === 1, 'busca do colaborador precisa ser persistida e insensível a caixa');
    searchSummaryAssert(count($collaboratorController->list($owner, ['status' => 'em_atendimento'])) >= 1, 'filtro combinado precisa localizar atendimento');
    searchSummaryAssert(count($technicianController->queue($technician, ['busca' => (string) $openNumber, 'prioridade' => 'baixa'])) === 1, 'fila técnica precisa combinar número e prioridade');

    $during = $administrationController->summary($administrator);
    searchSummaryAssert($during['total'] === $before['total'] + 2, 'resumo precisa refletir dois novos chamados');
    searchSummaryAssert($during['aberto'] === $before['aberto'] + 1, 'resumo precisa refletir chamado aberto');
    searchSummaryAssert($during['em_atendimento'] === $before['em_atendimento'] + 1, 'resumo precisa refletir atendimento');
    searchSummaryAssert(($during['by_priority']['baixa'] ?? 0) === ($before['by_priority']['baixa'] ?? 0) + 1, 'distribuição precisa refletir prioridade baixa');
    searchSummaryAssert(($during['by_priority']['alta'] ?? 0) === ($before['by_priority']['alta'] ?? 0) + 1, 'distribuição precisa refletir prioridade alta');
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

searchSummaryAssert($administrationController->summary($administrator) === $before, 'teste não pode deixar alterações no resumo');
echo "OK: busca, filtros e resumo persistidos validados sem resíduos.\n";
