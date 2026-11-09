<?php
declare(strict_types=1);

$root = dirname(__DIR__);

function renderPage(string $file, array $variables = [], array $get = [], array $post = []): string
{
    $_GET = $get;
    $_POST = $post;
    $_SERVER['REQUEST_METHOD'] = $post === [] ? 'GET' : 'POST';
    extract($variables, EXTR_SKIP);

    ob_start();
    include $file;
    return (string) ob_get_clean();
}

function assertContains(string $needle, string $html, string $message): void
{
    if (!str_contains($html, $needle)) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

function assertNotContains(string $needle, string $html, string $message): void
{
    if (str_contains($html, $needle)) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$list = renderPage($root . '/frontend/pages/meus-chamados.php', [
    'currentUserName' => 'Mariana & Oliveira',
    'tickets' => [],
]);
assertContains('Meus chamados', $list, 'a lista precisa de título');
assertContains('Nenhum chamado encontrado', $list, 'a lista precisa de estado vazio');
assertContains('Mariana &amp; Oliveira', $list, 'o nome do usuário deve ser escapado');
assertNotContains('#104', $list, 'a produção não pode inventar chamados');

$tickets = [[
    'id' => 104,
    'number' => 104,
    'title' => '<Impressora>',
    'category' => 'Impressora',
    'priority' => 'media',
    'status' => 'aberto',
]];
$listWithData = renderPage($root . '/frontend/pages/meus-chamados.php', [
    'currentUserName' => 'Mariana Oliveira',
    'tickets' => $tickets,
]);
assertContains('#104', $listWithData, 'a lista precisa renderizar dados recebidos');
assertContains('&lt;Impressora&gt;', $listWithData, 'o título do chamado deve ser escapado');
assertContains('ticket-card', $listWithData, 'a lista precisa de alternativa mobile');

$invalidForm = renderPage($root . '/frontend/pages/novo-chamado.php', [
    'currentUserName' => 'Mariana Oliveira',
    'categories' => ['impressora' => 'Impressora'],
], [], [
    'title' => '',
    'category' => 'impressora',
    'priority' => 'media',
    'description' => '',
]);
assertContains('Confira os campos obrigatórios', $invalidForm, 'o formulário precisa do estado inválido do Figma');
assertContains('aria-invalid="true"', $invalidForm, 'campos inválidos precisam ser anunciados');

$validForm = renderPage($root . '/frontend/pages/novo-chamado.php', [
    'currentUserName' => 'Mariana Oliveira',
    'categories' => ['impressora' => 'Impressora'],
], [], [
    'title' => 'Impressora não imprime',
    'category' => 'impressora',
    'priority' => 'media',
    'description' => 'A impressora está ligada, mas não imprime.',
]);
assertContains('integração com o servidor', $validForm, 'um POST válido não pode simular persistência');
assertNotContains('Chamado aberto com sucesso', $validForm, 'não pode haver sucesso fictício');

$missingDetail = renderPage($root . '/frontend/pages/chamado.php', [
    'currentUserName' => 'Mariana Oliveira',
    'ticket' => null,
]);
assertContains('Chamado indisponível', $missingDetail, 'o detalhe precisa falhar sem revelar dados');

$ticket = [
    'id' => 104,
    'number' => 104,
    'title' => 'Impressora não imprime',
    'category' => 'Impressora',
    'priority' => 'media',
    'status' => 'aberto',
    'description' => 'A impressora está ligada, mas não imprime.',
    'requester' => 'Mariana Oliveira',
    'assignee' => null,
    'history' => [['date' => '16/10, 09:00', 'text' => 'Mariana abriu este chamado.']],
];
$detail = renderPage($root . '/frontend/pages/chamado.php', [
    'currentUserName' => 'Mariana Oliveira',
    'ticket' => $ticket,
]);
assertContains('Chamado #104', $detail, 'o detalhe precisa renderizar o número');
assertContains('Comentários e histórico', $detail, 'o detalhe precisa da região de histórico');
assertContains('method="post"', $detail, 'comentários precisam usar POST');

echo "OK: fluxo do colaborador validado.\n";
