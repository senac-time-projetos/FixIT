<?php
declare(strict_types=1);

$root = dirname(__DIR__);

function renderTechnicianPage(string $file, array $variables = [], array $get = [], array $post = []): string
{
    $_GET = $get;
    $_POST = $post;
    $_SERVER['REQUEST_METHOD'] = $post === [] ? 'GET' : 'POST';
    extract($variables, EXTR_SKIP);
    ob_start();
    include $file;
    return (string) ob_get_clean();
}

function technicianAssertContains(string $needle, string $html, string $message): void
{
    if (!str_contains($html, $needle)) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

function technicianAssertNotContains(string $needle, string $html, string $message): void
{
    if (str_contains($html, $needle)) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$queue = renderTechnicianPage($root . '/frontend/pages/chamados.php', [
    'currentUserName' => 'Douglas Gomes',
    'tickets' => [],
    'categories' => ['impressora' => 'Impressora'],
]);
technicianAssertContains('Organize a fila e assuma os atendimentos.', $queue, 'a fila precisa do texto do Figma');
technicianAssertContains('Nenhum chamado encontrado', $queue, 'a fila precisa de estado vazio');
technicianAssertNotContains('#104', $queue, 'a produção não pode inventar chamados');

$tickets = [[
    'id' => 104, 'number' => 104, 'title' => '<Impressora>', 'requester' => 'Mariana Oliveira',
    'category' => 'Impressora', 'priority' => 'media', 'status' => 'aberto', 'assignee' => null,
]];
$queueWithData = renderTechnicianPage($root . '/frontend/pages/chamados.php', [
    'currentUserName' => 'Douglas Gomes', 'tickets' => $tickets, 'categories' => [],
]);
technicianAssertContains('&lt;Impressora&gt;', $queueWithData, 'a fila precisa escapar dados');
technicianAssertContains('Assumir →', $queueWithData, 'chamado livre precisa da ação de assumir');
technicianAssertContains('Chamados', $queueWithData, 'o cabeçalho do técnico precisa usar Chamados');

$openTicket = [
    'id' => 104, 'number' => 104, 'title' => 'Impressora não imprime', 'requester' => 'Mariana Oliveira',
    'department' => 'Administrativo', 'category' => 'Impressora', 'priority' => 'media', 'status' => 'aberto',
    'description' => 'A impressora está ligada, mas não imprime.', 'assignee' => null, 'assignee_id' => null,
    'history' => [['date' => '16/10, 09:00', 'text' => 'Mariana abriu este chamado.']],
];
$assume = renderTechnicianPage($root . '/frontend/pages/atender-chamado.php', [
    'currentUserName' => 'Douglas Gomes', 'currentTechnicianId' => 7, 'ticket' => $openTicket,
]);
technicianAssertContains('Atender chamado #104', $assume, 'chamado livre precisa da tela de atribuição');
technicianAssertContains('name="action" value="assume"', $assume, 'assumir precisa usar uma ação POST explícita');

$assumePost = renderTechnicianPage($root . '/frontend/pages/atender-chamado.php', [
    'currentUserName' => 'Douglas Gomes', 'currentTechnicianId' => 7, 'ticket' => $openTicket,
], [], ['action' => 'assume']);
technicianAssertContains('integração com o servidor', $assumePost, 'atribuição não pode simular persistência');

$activeTicket = $openTicket;
$activeTicket['status'] = 'em_atendimento';
$activeTicket['assignee'] = 'Douglas Gomes';
$activeTicket['assignee_id'] = 7;
$invalidService = renderTechnicianPage($root . '/frontend/pages/atender-chamado.php', [
    'currentUserName' => 'Douglas Gomes', 'currentTechnicianId' => 7, 'ticket' => $activeTicket,
], [], ['action' => 'close', 'solution' => 'curta']);
technicianAssertContains('entre 10 e 2000 caracteres', $invalidService, 'a solução precisa ser validada');
technicianAssertContains('aria-invalid="true"', $invalidService, 'erro precisa ser anunciado');

$validService = renderTechnicianPage($root . '/frontend/pages/atender-chamado.php', [
    'currentUserName' => 'Douglas Gomes', 'currentTechnicianId' => 7, 'ticket' => $activeTicket,
], [], ['action' => 'update', 'update' => 'Verificando a conexão da impressora.']);
technicianAssertContains('integração com o servidor', $validService, 'atualização válida não pode simular persistência');

$forbiddenTicket = $activeTicket;
$forbiddenTicket['assignee_id'] = 8;
$forbidden = renderTechnicianPage($root . '/frontend/pages/atender-chamado.php', [
    'currentUserName' => 'Douglas Gomes', 'currentTechnicianId' => 7, 'ticket' => $forbiddenTicket,
]);
technicianAssertContains('Atendimento indisponível', $forbidden, 'outro técnico não pode acessar o atendimento');

$closedTicket = $activeTicket;
$closedTicket['status'] = 'encerrado';
$closedTicket['solution'] = 'Cabo USB reconectado e fila de impressão reiniciada.';
$closedTicket['resolved_by'] = 'Douglas Gomes';
$closedTicket['resolved_at'] = '16/10, 10:00';
$closed = renderTechnicianPage($root . '/frontend/pages/chamado.php', [
    'currentUserName' => 'Mariana Oliveira', 'ticket' => $closedTicket,
]);
technicianAssertContains('Chamado #104 encerrado', $closed, 'o detalhe encerrado precisa do título do Figma');
technicianAssertContains('Resolvido por Douglas Gomes', $closed, 'a solução precisa identificar o técnico');
technicianAssertNotContains('Adicionar comentário', $closed, 'chamado encerrado precisa ser somente leitura');

echo "OK: fluxo do técnico validado.\n";
