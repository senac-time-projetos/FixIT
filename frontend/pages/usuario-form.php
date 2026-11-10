<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
$allowedRoles = ['administrador'];
require __DIR__ . '/../components/session_context.php';
require __DIR__ . '/../components/admin_context.php';

$user = is_array($user ?? null) ? $user : null;
$isEditing = $user !== null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$values = [
    'name' => (string) ($user['name'] ?? ''),
    'email' => (string) ($user['email'] ?? ''),
    'role' => (string) ($user['role'] ?? 'colaborador'),
    'status' => (string) ($user['status'] ?? 'ativo'),
    'password' => '',
];
$errors = [];
$serverError = '';
$allowedRoles = ['colaborador', 'tecnico', 'administrador'];
$allowedStatuses = ['ativo', 'inativo'];

if ($requestMethod === 'POST') {
    foreach (array_keys($values) as $key) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $action = (string) ($_POST['action'] ?? 'save');
    if ($action === 'deactivate' && $isEditing) {
        $serverError = 'Não foi possível desativar o usuário sem a integração com o servidor. Tente novamente mais tarde.';
    } else {
        $nameLength = mb_strlen($values['name']);
        if ($nameLength < 2 || $nameLength > 120) {
            $errors['name'] = 'Informe um nome entre 2 e 120 caracteres.';
        }
        if (mb_strlen($values['email']) > 160 || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Informe um e-mail válido de até 160 caracteres.';
        }
        if (!in_array($values['role'], $allowedRoles, true)) {
            $errors['role'] = 'Selecione um perfil válido.';
        }
        if (!in_array($values['status'], $allowedStatuses, true)) {
            $errors['status'] = 'Selecione uma situação válida.';
        }
        $passwordLength = mb_strlen($values['password']);
        if ((!$isEditing && $passwordLength < 8) || ($isEditing && $passwordLength > 0 && $passwordLength < 8)) {
            $errors['password'] = $isEditing ? 'A nova senha deve ter pelo menos 8 caracteres.' : 'A senha inicial deve ter pelo menos 8 caracteres.';
        }
        if ($errors === []) {
            $serverError = 'Não foi possível salvar o usuário sem a integração com o servidor. Tente novamente mais tarde.';
        }
    }
}

$headerActive = 'users';
$title = $isEditing ? 'Editar usuário' : 'Cadastrar usuário';
if (!function_exists('userFieldState')) {
    function userFieldState(string $key, array $errors): string
    {
        return isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="' . $key . '-error"' : '';
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= fixit_e($title) ?> no FixIT."><title><?= fixit_e($title) ?> — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"><link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
<?php require __DIR__ . '/../components/header.php'; ?>
<?php if ($adminAccessDenied): ?>
<main class="app-content"><div class="page-heading"><h1>Acesso não autorizado</h1><p>Seu perfil não tem permissão para acessar a administração.</p></div><a class="button button--primary" href="<?= fixit_e($pageBase) ?>/login.php">Voltar ao login</a></main>
<?php else: ?>
<main class="app-content admin-content">
    <div class="page-heading"><h1><?= fixit_e($title) ?></h1><p><?= $errors !== [] ? 'Confira os campos obrigatórios antes de continuar.' : 'O administrador define o perfil e libera o acesso.' ?></p></div>
    <?php if ($serverError !== ''): ?><p class="message message--error" role="alert"><?= fixit_e($serverError) ?></p><?php endif; ?>
    <form class="surface-card admin-form" method="post" action="<?= fixit_e($pageBase) ?>/usuario-form.php<?= $isEditing ? '?id=' . rawurlencode((string) ($user['id'] ?? '')) : '' ?>" novalidate>
        <div class="form-field"><label for="name">Nome *</label><input id="name" name="name" maxlength="120" value="<?= fixit_e($values['name']) ?>"<?= userFieldState('name', $errors) ?><?= isset($errors['name']) ? ' autofocus' : '' ?>><?php if (isset($errors['name'])): ?><p class="form-field__error" id="name-error"><?= fixit_e($errors['name']) ?></p><?php endif; ?></div>
        <div class="form-field"><label for="email">E-mail *</label><input id="email" name="email" type="email" maxlength="160" value="<?= fixit_e($values['email']) ?>"<?= userFieldState('email', $errors) ?>><?php if (isset($errors['email'])): ?><p class="form-field__error" id="email-error"><?= fixit_e($errors['email']) ?></p><?php endif; ?></div>
        <div class="form-grid"><div class="form-field"><label for="role">Perfil *</label><select id="role" name="role"<?= userFieldState('role', $errors) ?>><?php foreach (['colaborador' => 'Colaborador', 'tecnico' => 'Técnico', 'administrador' => 'Administrador'] as $value => $label): ?><option value="<?= $value ?>"<?= $values['role'] === $value ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select><?php if (isset($errors['role'])): ?><p class="form-field__error" id="role-error"><?= fixit_e($errors['role']) ?></p><?php endif; ?></div><div class="form-field"><label for="status">Situação *</label><select id="status" name="status"<?= userFieldState('status', $errors) ?>><option value="ativo"<?= $values['status'] === 'ativo' ? ' selected' : '' ?>>Ativo</option><option value="inativo"<?= $values['status'] === 'inativo' ? ' selected' : '' ?>>Inativo</option></select><?php if (isset($errors['status'])): ?><p class="form-field__error" id="status-error"><?= fixit_e($errors['status']) ?></p><?php endif; ?></div></div>
        <div class="form-field"><label for="password"><?= $isEditing ? 'Nova senha (opcional)' : 'Senha inicial *' ?></label><input id="password" name="password" type="password" autocomplete="new-password"<?= userFieldState('password', $errors) ?>><?php if (isset($errors['password'])): ?><p class="form-field__error" id="password-error"><?= fixit_e($errors['password']) ?></p><?php endif; ?></div>
        <div class="form-actions"><button class="button button--primary" type="submit" name="action" value="save"><?= $isEditing ? 'Salvar alterações' : 'Salvar usuário' ?></button><a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/usuarios.php">Cancelar</a></div>
    </form>
    <?php if ($isEditing && ($user['status'] ?? '') === 'ativo'): ?><form method="post" action="<?= fixit_e($pageBase) ?>/usuario-form.php?id=<?= rawurlencode((string) ($user['id'] ?? '')) ?>"><button class="button button--secondary" type="submit" name="action" value="deactivate">Desativar usuário</button></form><?php endif; ?>
</main>
<?php endif; ?>
</body>
</html>
