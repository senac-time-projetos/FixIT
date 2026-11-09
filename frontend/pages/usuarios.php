<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
require __DIR__ . '/../components/admin_context.php';

$users = is_array($users ?? null) ? $users : [];
$successMessage = trim((string) ($successMessage ?? ''));
$headerActive = 'users';
$roleLabels = ['colaborador' => 'Colaborador', 'tecnico' => 'Técnico', 'administrador' => 'Administrador'];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Gerencie os usuários do FixIT."><title>Usuários — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"><link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
<?php require __DIR__ . '/../components/header.php'; ?>
<?php if ($adminAccessDenied): ?>
<main class="app-content"><div class="page-heading"><h1>Acesso não autorizado</h1><p>Seu perfil não tem permissão para acessar a administração.</p></div><a class="button button--primary" href="<?= fixit_e($pageBase) ?>/login.php">Voltar ao login</a></main>
<?php else: ?>
<main class="app-content admin-content">
    <div class="page-heading"><h1>Usuários</h1><p<?= $successMessage !== '' ? ' role="status"' : '' ?>><?= $successMessage !== '' ? fixit_e($successMessage) : 'Cadastre colaboradores e técnicos e controle o acesso.' ?></p></div>
    <a class="button button--primary" href="<?= fixit_e($pageBase) ?>/usuario-form.php">+ Novo usuário</a>
    <?php if ($users === []): ?>
        <section class="empty-state"><h2>Nenhum usuário encontrado</h2><p>Cadastre o primeiro usuário para liberar o acesso.</p></section>
    <?php else: ?>
        <div class="ticket-table-wrap"><table class="ticket-table admin-table"><thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th>Ação</th></tr></thead><tbody>
        <?php foreach ($users as $user): ?><tr><td><?= fixit_e($user['name'] ?? '') ?></td><td><?= fixit_e($user['email'] ?? '') ?></td><td><?= fixit_e($roleLabels[$user['role'] ?? ''] ?? 'Indisponível') ?></td><td><?= ($user['status'] ?? '') === 'ativo' ? 'Ativo' : 'Inativo' ?></td><td><a href="<?= fixit_e($pageBase) ?>/usuario-form.php?id=<?= rawurlencode((string) ($user['id'] ?? '')) ?>">Editar →</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <section class="admin-cards" aria-label="Usuários"><?php foreach ($users as $user): ?><article class="user-card"><h2><?= fixit_e($user['name'] ?? '') ?></h2><p><?= fixit_e($user['email'] ?? '') ?></p><dl><div><dt>Perfil</dt><dd><?= fixit_e($roleLabels[$user['role'] ?? ''] ?? 'Indisponível') ?></dd></div><div><dt>Situação</dt><dd><?= ($user['status'] ?? '') === 'ativo' ? 'Ativo' : 'Inativo' ?></dd></div></dl><a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/usuario-form.php?id=<?= rawurlencode((string) ($user['id'] ?? '')) ?>">Editar usuário</a></article><?php endforeach; ?></section>
    <?php endif; ?>
    <p class="form-help">Desative o acesso para preservar os chamados e o histórico do usuário.</p>
</main>
<?php endif; ?>
</body>
</html>
