<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$application = require $root . '/backend/bootstrap.php';
$pdo = $application['pdo'];
$users = $application['users'];
$categories = $application['categories'];
$service = $application['ticket_service'];
$controller = $application['technician_ticket_controller'];

function dockerTicketAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$ownerRow = $users->findByEmail('mariana@fixit.test');
$technicianRow = $users->findByEmail('douglas@fixit.test');
$categoryRows = $categories->list(true);
dockerTicketAssert($ownerRow !== null && $technicianRow !== null && $categoryRows !== [], 'seed precisa fornecer usuários e categoria');
$owner = ['id' => (int) $ownerRow['id'], 'nome' => (string) $ownerRow['nome'], 'perfil' => (string) $ownerRow['perfil']];
$technician = ['id' => (int) $technicianRow['id'], 'nome' => (string) $technicianRow['nome'], 'perfil' => (string) $technicianRow['perfil']];
$before = (int) $pdo->query('SELECT COUNT(*) FROM chamados')->fetchColumn();

$pdo->beginTransaction();
try {
    $number = $service->open($owner, [
        'titulo' => 'Fluxo Docker temporário',
        'descricao' => 'Chamado criado dentro de transação para validar o ciclo técnico completo.',
        'categoria_id' => (int) $categoryRows[0]['id'],
        'prioridade' => 'media',
    ]);
    dockerTicketAssert(count($controller->queue($technician, ['busca' => (string) $number])) === 1, 'fila MySQL precisa localizar o chamado');
    $controller->assign($technician, $number);
    $controller->update($technician, $number, 'Diagnóstico executado no ambiente Docker.');
    $controller->close($technician, $number, 'Serviço validado e chamado encerrado no teste Docker.');
    $detail = $controller->detail($technician, $number);
    dockerTicketAssert($detail !== null && $detail['status'] === 'encerrado', 'fluxo MySQL precisa chegar ao encerramento');
    dockerTicketAssert(count($detail['history']) === 4, 'histórico MySQL precisa registrar quatro eventos');
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

dockerTicketAssert((int) $pdo->query('SELECT COUNT(*) FROM chamados')->fetchColumn() === $before, 'teste MySQL não pode deixar dados residuais');
echo "OK: fluxo técnico completo validado no MySQL Docker sem resíduos.\n";
