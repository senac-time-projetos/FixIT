<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
require __DIR__ . '/../components/admin_context.php';

$categories = is_array($categories ?? null) ? $categories : [];
$category = is_array($category ?? null) ? $category : null;
$isEditing = $category !== null;
$successMessage = trim((string) ($successMessage ?? ''));
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$name = (string) ($category['name'] ?? '');
$nameError = '';
$serverError = '';
if ($requestMethod === 'POST') {
    $action = (string) ($_POST['action'] ?? 'save');
    $name = trim((string) ($_POST['name'] ?? $name));
    if ($action === 'deactivate' && $isEditing) {
        $serverError = 'Não foi possível inativar a categoria sem a integração com o servidor. Tente novamente mais tarde.';
    } else {
        $length = mb_strlen($name);
        if ($length < 2 || $length > 80) {
            $nameError = 'Informe um nome entre 2 e 80 caracteres.';
        } else {
            $serverError = 'Não foi possível salvar a categoria sem a integração com o servidor. Tente novamente mais tarde.';
        }
    }
}
$headerActive = 'categories';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Gerencie as categorias do FixIT."><title><?= $isEditing ? 'Editar categoria' : 'Categorias' ?> — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"><link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
<?php require __DIR__ . '/../components/header.php'; ?>
<?php if ($adminAccessDenied): ?>
<main class="app-content"><div class="page-heading"><h1>Acesso não autorizado</h1><p>Seu perfil não tem permissão para acessar a administração.</p></div><a class="button button--primary" href="<?= fixit_e($pageBase) ?>/login.php">Voltar ao login</a></main>
<?php else: ?>
<main class="app-content admin-content">
    <div class="page-heading"><h1><?= $isEditing ? 'Editar categoria' : 'Categorias' ?></h1><p<?= $successMessage !== '' ? ' role="status"' : '' ?>><?= $successMessage !== '' ? fixit_e($successMessage) : 'Mantenha uma lista curta para facilitar a abertura de chamados.' ?></p></div>
    <?php if ($serverError !== ''): ?><p class="message message--error" role="alert"><?= fixit_e($serverError) ?></p><?php endif; ?>
    <form class="surface-card category-form" method="post" action="<?= fixit_e($pageBase) ?>/categorias.php<?= $isEditing ? '?id=' . rawurlencode((string) ($category['id'] ?? '')) : '' ?>" novalidate>
        <div class="form-field"><label for="name">Nome da categoria *</label><input id="name" name="name" maxlength="80" value="<?= fixit_e($name) ?>"<?= $nameError !== '' ? ' aria-invalid="true" aria-describedby="name-error" autofocus' : '' ?>><?php if ($nameError !== ''): ?><p class="form-field__error" id="name-error"><?= fixit_e($nameError) ?></p><?php endif; ?></div>
        <button class="button button--primary" type="submit" name="action" value="save"><?= $isEditing ? 'Salvar alterações' : 'Adicionar categoria' ?></button>
    </form>
    <?php if ($categories === []): ?>
        <section class="empty-state"><h2>Nenhuma categoria encontrada</h2><p>Adicione uma categoria ativa para organizar os chamados.</p></section>
    <?php else: ?>
        <div class="ticket-table-wrap"><table class="ticket-table category-table"><thead><tr><th>Categoria</th><th>Situação</th><th>Ação</th></tr></thead><tbody><?php foreach ($categories as $item): ?><tr><td><?= fixit_e($item['name'] ?? '') ?></td><td><?= ($item['status'] ?? '') === 'ativo' ? 'Ativa' : 'Inativa' ?></td><td><a href="<?= fixit_e($pageBase) ?>/categorias.php?id=<?= rawurlencode((string) ($item['id'] ?? '')) ?>">Editar →</a></td></tr><?php endforeach; ?></tbody></table></div>
        <section class="admin-cards" aria-label="Categorias"><?php foreach ($categories as $item): ?><article class="category-card"><h2><?= fixit_e($item['name'] ?? '') ?></h2><p><strong>Situação:</strong> <?= ($item['status'] ?? '') === 'ativo' ? 'Ativa' : 'Inativa' ?></p><a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/categorias.php?id=<?= rawurlencode((string) ($item['id'] ?? '')) ?>">Editar categoria</a></article><?php endforeach; ?></section>
    <?php endif; ?>
    <p class="form-help">Inative categorias sem apagar o histórico dos chamados.</p>
    <?php if ($isEditing && ($category['status'] ?? '') === 'ativo'): ?><form method="post" action="<?= fixit_e($pageBase) ?>/categorias.php?id=<?= rawurlencode((string) ($category['id'] ?? '')) ?>"><button class="button button--secondary" type="submit" name="action" value="deactivate">Inativar categoria</button></form><?php endif; ?>
</main>
<?php endif; ?>
</body>
</html>
