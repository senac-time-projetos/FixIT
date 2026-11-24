<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
$allowedRoles = ['administrador'];
require __DIR__ . '/../components/session_context.php';
require __DIR__ . '/../components/admin_context.php';

$controller = null;
$actor = ['id' => (int) ($currentUserId ?? 0), 'nome' => (string) ($currentUserName ?? 'Administrador'), 'perfil' => (string) ($currentUserRole ?? 'administrador')];
$requestedId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
if (!$skipAuth) {
    $application = require __DIR__ . '/../../backend/bootstrap.php';
    $controller = $application['administration_controller'];
    $categories = $controller->categories($actor);
    $category = $requestedId === null ? null : $controller->category($actor, $requestedId);
} else {
    $categories = is_array($categories ?? null) ? $categories : [];
    $category = is_array($category ?? null) ? $category : null;
    $requestedId = $requestedId ?? (isset($category['id']) ? (int) $category['id'] : null);
}
$isEditing = $category !== null;
$notFound = $requestedId !== null && $category === null;
$successMessage = trim((string) ($successMessage ?? ''));
$successMessage = !$skipAuth && isset($_GET['saved']) ? 'Categoria salva com sucesso.' : $successMessage;
$successMessage = !$skipAuth && isset($_GET['deactivated']) ? 'Categoria inativada com sucesso.' : $successMessage;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$name = (string) ($category['name'] ?? '');
$description = (string) ($category['description'] ?? '');
$status = (string) ($category['status'] ?? 'ativo');
$nameError = '';
$descriptionError = '';
$statusError = '';
$serverError = '';
if ($requestMethod === 'POST') {
    $action = (string) ($_POST['action'] ?? 'save');
    $name = trim((string) ($_POST['name'] ?? $name));
    $description = trim((string) ($_POST['description'] ?? $description));
    $status = (string) ($_POST['status'] ?? $status);
    if (!$skipAuth && !$csrf->validate((string) ($_POST['_csrf'] ?? ''))) {
        $serverError = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif ($notFound) {
        $serverError = 'Categoria não encontrada.';
    } elseif ($action === 'deactivate' && $isEditing) {
        if ($skipAuth) {
            $serverError = 'Não foi possível inativar a categoria sem a integração com o servidor. Tente novamente mais tarde.';
        } else {
            try {
                $controller->deactivateCategory($actor, (int) $category['id']);
                header('Location: categorias.php?deactivated=1');
                exit;
            } catch (DomainException $exception) {
                $serverError = $exception->getMessage();
            } catch (Throwable) {
                $serverError = 'Não foi possível inativar a categoria. Tente novamente mais tarde.';
            }
        }
    } else {
        $length = mb_strlen($name);
        if ($length < 2 || $length > 80) {
            $nameError = 'Informe um nome entre 2 e 80 caracteres.';
        }
        if (mb_strlen($description) > 255) {
            $descriptionError = 'A descrição deve ter no máximo 255 caracteres.';
        }
        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $statusError = 'Selecione uma situação válida.';
        }
        if ($nameError === '' && $descriptionError === '' && $statusError === '' && $serverError === '') {
            if ($skipAuth) {
                $serverError = 'Não foi possível salvar a categoria sem a integração com o servidor. Tente novamente mais tarde.';
            } else {
                try {
                    $controller->saveCategory($actor, $isEditing ? (int) $category['id'] : null, ['name' => $name, 'description' => $description, 'status' => $status]);
                    header('Location: categorias.php?saved=1');
                    exit;
                } catch (DomainException $exception) {
                    $serverError = $exception->getMessage();
                } catch (Throwable) {
                    $serverError = 'Não foi possível salvar a categoria. Tente novamente mais tarde.';
                }
            }
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
    <?php if ($notFound): ?>
    <div class="page-heading"><h1>Categoria não encontrada</h1><p>O cadastro solicitado não existe ou não está mais disponível.</p></div>
    <a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/categorias.php">Voltar para categorias</a>
    <?php else: ?>
    <div class="page-heading"><h1><?= $isEditing ? 'Editar categoria' : 'Categorias' ?></h1><p<?= $successMessage !== '' ? ' role="status"' : '' ?>><?= $successMessage !== '' ? fixit_e($successMessage) : 'Mantenha uma lista curta para facilitar a abertura de chamados.' ?></p></div>
    <?php if ($serverError !== ''): ?><p class="message message--error" role="alert"><?= fixit_e($serverError) ?></p><?php endif; ?>
    <form class="surface-card category-form" method="post" action="<?= fixit_e($pageBase) ?>/categorias.php<?= $isEditing ? '?id=' . rawurlencode((string) ($category['id'] ?? '')) : '' ?>" novalidate>
        <input type="hidden" name="_csrf" value="<?= fixit_e($skipAuth ? 'fixture-token' : $csrf->token()) ?>">
        <div class="form-field"><label for="name">Nome da categoria *</label><input id="name" name="name" maxlength="80" value="<?= fixit_e($name) ?>"<?= $nameError !== '' ? ' aria-invalid="true" aria-describedby="name-error" autofocus' : '' ?>><?php if ($nameError !== ''): ?><p class="form-field__error" id="name-error"><?= fixit_e($nameError) ?></p><?php endif; ?></div>
        <div class="form-field"><label for="description">Descrição</label><textarea id="description" name="description" maxlength="255" rows="3"<?= $descriptionError !== '' ? ' aria-invalid="true" aria-describedby="description-error"' : '' ?>><?= fixit_e($description) ?></textarea><?php if ($descriptionError !== ''): ?><p class="form-field__error" id="description-error"><?= fixit_e($descriptionError) ?></p><?php endif; ?></div>
        <?php if ($isEditing): ?><div class="form-field"><label for="status">Situação *</label><select id="status" name="status"<?= $statusError !== '' ? ' aria-invalid="true" aria-describedby="status-error"' : '' ?>><option value="ativo"<?= $status === 'ativo' ? ' selected' : '' ?>>Ativa</option><option value="inativo"<?= $status === 'inativo' ? ' selected' : '' ?>>Inativa</option></select><?php if ($statusError !== ''): ?><p class="form-field__error" id="status-error"><?= fixit_e($statusError) ?></p><?php endif; ?></div><?php else: ?><input type="hidden" name="status" value="ativo"><?php endif; ?>
        <button class="button button--primary" type="submit" name="action" value="save"><?= $isEditing ? 'Salvar alterações' : 'Adicionar categoria' ?></button>
    </form>
    <?php if ($categories === []): ?>
        <section class="empty-state"><h2>Nenhuma categoria encontrada</h2><p>Adicione uma categoria ativa para organizar os chamados.</p></section>
    <?php else: ?>
        <div class="ticket-table-wrap"><table class="ticket-table category-table"><thead><tr><th>Categoria</th><th>Situação</th><th>Ação</th></tr></thead><tbody><?php foreach ($categories as $item): ?><tr><td><?= fixit_e($item['name'] ?? '') ?></td><td><?= ($item['status'] ?? '') === 'ativo' ? 'Ativa' : 'Inativa' ?></td><td><a href="<?= fixit_e($pageBase) ?>/categorias.php?id=<?= rawurlencode((string) ($item['id'] ?? '')) ?>">Editar →</a></td></tr><?php endforeach; ?></tbody></table></div>
        <section class="admin-cards" aria-label="Categorias"><?php foreach ($categories as $item): ?><article class="category-card"><h2><?= fixit_e($item['name'] ?? '') ?></h2><p><strong>Situação:</strong> <?= ($item['status'] ?? '') === 'ativo' ? 'Ativa' : 'Inativa' ?></p><a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/categorias.php?id=<?= rawurlencode((string) ($item['id'] ?? '')) ?>">Editar categoria</a></article><?php endforeach; ?></section>
    <?php endif; ?>
    <p class="form-help">Inative categorias sem apagar o histórico dos chamados.</p>
    <?php if ($isEditing && ($category['status'] ?? '') === 'ativo'): ?><form method="post" action="<?= fixit_e($pageBase) ?>/categorias.php?id=<?= rawurlencode((string) ($category['id'] ?? '')) ?>"><input type="hidden" name="_csrf" value="<?= fixit_e($skipAuth ? 'fixture-token' : $csrf->token()) ?>"><button class="button button--secondary" type="submit" name="action" value="deactivate">Inativar categoria</button></form><?php endif; ?>
    <?php endif; ?>
</main>
<?php endif; ?>
</body>
</html>
