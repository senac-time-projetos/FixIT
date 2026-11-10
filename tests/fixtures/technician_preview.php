<?php
declare(strict_types=1);

$screen = (string) ($_GET['screen'] ?? 'queue');
$skipAuth = true;
$assetBase = '/frontend';
$pageBase = '/frontend/pages';
$currentUserName = $screen === 'closed' ? 'Mariana Oliveira' : 'Douglas Gomes';
$currentTechnicianId = 7;
$categories = ['impressora' => 'Impressora', 'acesso' => 'Acesso', 'software' => 'Software'];
$base = [
    'id' => 104, 'number' => 104, 'title' => 'Impressora não imprime', 'requester' => 'Mariana Oliveira',
    'department' => 'Administrativo', 'category' => 'Impressora', 'category_key' => 'impressora',
    'priority' => 'media', 'status' => 'aberto', 'description' => 'A impressora do administrativo está ligada, mas não imprime desde esta manhã.',
    'assignee' => null, 'assignee_id' => null,
    'history' => [['date' => '16/10, 09:00', 'text' => 'Mariana abriu este chamado.']],
];

if ($screen === 'queue') {
    $tickets = [
        $base,
        array_merge($base, ['id' => 103, 'number' => 103, 'title' => 'Sem acesso', 'category' => 'Acesso', 'category_key' => 'acesso', 'priority' => 'alta', 'status' => 'em_atendimento', 'assignee' => 'Douglas', 'assignee_id' => 7]),
        array_merge($base, ['id' => 102, 'number' => 102, 'title' => 'Instalação software', 'category' => 'Software', 'category_key' => 'software', 'priority' => 'baixa', 'status' => 'encerrado', 'assignee' => 'Douglas', 'assignee_id' => 7]),
    ];
    require dirname(__DIR__, 2) . '/frontend/pages/chamados.php';
    return;
}

$ticket = $base;
if ($screen === 'service') {
    $ticket['status'] = 'em_atendimento';
    $ticket['assignee'] = 'Douglas Gomes';
    $ticket['assignee_id'] = 7;
    require dirname(__DIR__, 2) . '/frontend/pages/atender-chamado.php';
    return;
}
if ($screen === 'closed') {
    $ticket['status'] = 'encerrado';
    $ticket['assignee'] = 'Douglas Gomes';
    $ticket['assignee_id'] = 7;
    $ticket['solution'] = 'Cabo USB reconectado e fila de impressão reiniciada. A impressão de teste foi concluída com sucesso.';
    $ticket['resolved_by'] = 'Douglas Gomes';
    $ticket['resolved_at'] = '16/10, 10:00';
    $ticket['history'][] = ['date' => '16/10, 09:15', 'text' => 'Douglas assumiu o atendimento.'];
    $ticket['history'][] = ['date' => '16/10, 09:40', 'text' => 'Conexão e fila de impressão verificadas.'];
    $ticket['history'][] = ['date' => '16/10, 10:00', 'text' => 'Chamado encerrado com solução registrada.'];
    require dirname(__DIR__, 2) . '/frontend/pages/chamado.php';
    return;
}

require dirname(__DIR__, 2) . '/frontend/pages/atender-chamado.php';
