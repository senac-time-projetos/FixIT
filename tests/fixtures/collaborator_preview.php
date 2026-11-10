<?php
declare(strict_types=1);

$screen = (string) ($_GET['screen'] ?? 'list');
$skipAuth = true;
$assetBase = '/frontend';
$pageBase = '/frontend/pages';
$currentUserName = 'Mariana Oliveira';
$categories = [
    'impressora' => 'Impressora',
    'acesso' => 'Acesso',
    'software' => 'Software',
];
$tickets = [
    ['id' => 104, 'number' => 104, 'title' => 'Impressora não imprime', 'category' => 'Impressora', 'priority' => 'media', 'status' => 'aberto'],
    ['id' => 103, 'number' => 103, 'title' => 'Sem acesso ao sistema', 'category' => 'Acesso', 'priority' => 'alta', 'status' => 'em_atendimento'],
    ['id' => 102, 'number' => 102, 'title' => 'Instalação de software', 'category' => 'Software', 'priority' => 'baixa', 'status' => 'encerrado'],
];
$ticket = [
    'id' => 104,
    'number' => 104,
    'title' => 'Impressora não imprime',
    'category' => 'Impressora',
    'priority' => 'media',
    'status' => 'aberto',
    'description' => 'A impressora do administrativo está ligada, mas não imprime desde esta manhã.',
    'requester' => 'Mariana Oliveira',
    'assignee' => null,
    'history' => [['date' => '16/10, 09:00', 'text' => 'Mariana abriu este chamado.']],
];

if ($screen === 'new' || $screen === 'invalid') {
    $initialForm = $screen === 'new' ? [
        'title' => 'Impressora não imprime',
        'category' => 'impressora',
        'priority' => 'media',
        'description' => 'A impressora do administrativo está ligada, mas não imprime desde esta manhã.',
    ] : ['category' => 'impressora', 'priority' => 'media'];
    if ($screen === 'invalid') {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['title' => '', 'category' => 'impressora', 'priority' => 'media', 'description' => ''];
    }
    require dirname(__DIR__, 2) . '/frontend/pages/novo-chamado.php';
    return;
}

if ($screen === 'detail') {
    $successMessage = 'Chamado aberto com sucesso. Acompanhe as atualizações por aqui.';
    require dirname(__DIR__, 2) . '/frontend/pages/chamado.php';
    return;
}

require dirname(__DIR__, 2) . '/frontend/pages/meus-chamados.php';
