<?php
declare(strict_types=1);

$root = dirname(__DIR__);

function renderAdminPage(string $file, array $variables = [], array $get = [], array $post = []): string
{
    $_GET = $get;
    $_POST = $post;
    $_SERVER['REQUEST_METHOD'] = $post === [] ? 'GET' : 'POST';
    extract($variables, EXTR_SKIP);
    ob_start();
    include $file;
    return (string) ob_get_clean();
}

function adminContains(string $needle, string $html, string $message): void
{
    if (!str_contains($html, $needle)) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

function adminNotContains(string $needle, string $html, string $message): void
{
    if (str_contains($html, $needle)) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$summary = renderAdminPage($root . '/frontend/pages/resumo.php', [
    'currentUserName' => 'Administrador',
    'tickets' => [],
]);
adminContains('Resumo dos atendimentos', $summary, 'o resumo precisa do título do Figma');
adminContains('>0<', $summary, 'o resumo vazio precisa usar dados reais recebidos');
adminNotContains('>3<', $summary, 'a produção não pode inventar totais');
adminContains('Usuários', $summary, 'o cabeçalho precisa da navegação administrativa');

$denied = renderAdminPage($root . '/frontend/pages/resumo.php', [
    'currentUserName' => 'Colaborador', 'tickets' => [], 'adminAccessDenied' => true,
]);
adminContains('Acesso não autorizado', $denied, 'a administração precisa falhar sem revelar dados');
adminNotContains('Indicadores dos chamados', $denied, 'o estado negado não pode revelar o resumo');

$users = renderAdminPage($root . '/frontend/pages/usuarios.php', [
    'currentUserName' => 'Administrador',
    'users' => [],
]);
adminContains('Nenhum usuário encontrado', $users, 'usuários precisa de estado vazio');
adminNotContains('Mariana Oliveira', $users, 'a produção não pode inventar usuários');

$userData = [['id' => 1, 'name' => '<Mariana>', 'email' => 'mariana@fixit.com.br', 'role' => 'colaborador', 'status' => 'ativo']];
$usersWithData = renderAdminPage($root . '/frontend/pages/usuarios.php', [
    'currentUserName' => 'Administrador', 'users' => $userData,
]);
adminContains('&lt;Mariana&gt;', $usersWithData, 'a lista precisa escapar dados');
adminContains('user-card', $usersWithData, 'a lista precisa de cartões mobile');

$invalidUser = renderAdminPage($root . '/frontend/pages/usuario-form.php', [
    'currentUserName' => 'Administrador', 'user' => null,
], [], ['action' => 'save', 'name' => '', 'email' => 'invalido', 'role' => 'colaborador', 'status' => 'ativo', 'password' => '123']);
adminContains('Confira os campos obrigatórios', $invalidUser, 'cadastro precisa validar campos');
adminContains('aria-invalid="true"', $invalidUser, 'erros precisam ser anunciados');

$validUser = renderAdminPage($root . '/frontend/pages/usuario-form.php', [
    'currentUserName' => 'Administrador', 'user' => null,
], [], ['action' => 'save', 'name' => 'Ana Souza', 'email' => 'ana@fixit.com.br', 'role' => 'colaborador', 'status' => 'ativo', 'password' => 'segura123']);
adminContains('integração com o servidor', $validUser, 'cadastro válido não pode simular persistência');
adminNotContains('Usuário salvo com sucesso', $validUser, 'não pode haver sucesso fictício');

$editUser = renderAdminPage($root . '/frontend/pages/usuario-form.php', [
    'currentUserName' => 'Administrador',
    'user' => ['id' => 1, 'name' => 'Mariana Oliveira', 'email' => 'mariana@fixit.com.br', 'role' => 'colaborador', 'status' => 'ativo'],
]);
adminContains('Editar usuário', $editUser, 'edição precisa de título próprio');
adminContains('Desativar usuário', $editUser, 'edição precisa da ação de desativação');

$categories = renderAdminPage($root . '/frontend/pages/categorias.php', [
    'currentUserName' => 'Administrador', 'categories' => [],
]);
adminContains('Nenhuma categoria encontrada', $categories, 'categorias precisa de estado vazio');
adminNotContains('Impressora', $categories, 'a produção não pode inventar categorias');

$invalidCategory = renderAdminPage($root . '/frontend/pages/categorias.php', [
    'currentUserName' => 'Administrador', 'categories' => [],
], [], ['action' => 'save', 'name' => 'A']);
adminContains('entre 2 e 80 caracteres', $invalidCategory, 'categoria precisa ser validada');

echo "OK: telas administrativas validadas.\n";
