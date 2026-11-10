<?php
declare(strict_types=1);

$screen = (string) ($_GET['screen'] ?? 'summary');
$skipAuth = true;
$assetBase = '/frontend';
$pageBase = '/frontend/pages';
$currentUserName = 'Administrador';
$tickets = [
    ['status' => 'aberto', 'category' => 'Impressora', 'priority' => 'media'],
    ['status' => 'em_atendimento', 'category' => 'Acesso', 'priority' => 'alta'],
    ['status' => 'encerrado', 'category' => 'Software', 'priority' => 'baixa'],
];
$users = [
    ['id' => 1, 'name' => 'Mariana Oliveira', 'email' => 'mariana@fixit.com.br', 'role' => 'colaborador', 'status' => 'ativo'],
    ['id' => 2, 'name' => 'Douglas Gomes', 'email' => 'douglas@fixit.com.br', 'role' => 'tecnico', 'status' => 'ativo'],
    ['id' => 3, 'name' => 'Pedro Santiago', 'email' => 'pedro@fixit.com.br', 'role' => 'colaborador', 'status' => 'inativo'],
];
$categories = [
    ['id' => 1, 'name' => 'Impressora', 'status' => 'ativo'],
    ['id' => 2, 'name' => 'Acesso', 'status' => 'ativo'],
    ['id' => 3, 'name' => 'Software', 'status' => 'ativo'],
    ['id' => 4, 'name' => 'Rede', 'status' => 'ativo'],
];

if ($screen === 'summary') {
    require dirname(__DIR__, 2) . '/frontend/pages/resumo.php';
    return;
}
if ($screen === 'users' || $screen === 'user-saved' || $screen === 'user-deactivated') {
    if ($screen === 'user-saved') {
        $successMessage = 'Usuário salvo com sucesso.';
        $users[2] = ['id' => 3, 'name' => 'Ana Souza', 'email' => 'ana@fixit.com.br', 'role' => 'colaborador', 'status' => 'ativo'];
    } elseif ($screen === 'user-deactivated') {
        $successMessage = 'Acesso de Mariana desativado. Histórico preservado.';
        $users[0]['status'] = 'inativo';
    }
    require dirname(__DIR__, 2) . '/frontend/pages/usuarios.php';
    return;
}
if ($screen === 'user-new' || $screen === 'user-edit') {
    $user = $screen === 'user-edit' ? $users[0] : null;
    require dirname(__DIR__, 2) . '/frontend/pages/usuario-form.php';
    return;
}
if ($screen === 'category-edit') {
    $category = $categories[0];
} elseif ($screen === 'category-saved') {
    $successMessage = 'Categoria salva com sucesso.';
    $categories[3] = ['id' => 4, 'name' => 'Hardware', 'status' => 'ativo'];
}
require dirname(__DIR__, 2) . '/frontend/pages/categorias.php';
