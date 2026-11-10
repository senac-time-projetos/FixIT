<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
$allowedRoles = ['administrador'];
require __DIR__ . '/../components/session_context.php';
require __DIR__ . '/../components/admin_context.php';

$tickets = is_array($tickets ?? null) ? $tickets : [];
$summary = ['total' => count($tickets), 'aberto' => 0, 'em_atendimento' => 0, 'encerrado' => 0];
$byCategory = [];
$byPriority = [];
foreach ($tickets as $ticket) {
    $status = (string) ($ticket['status'] ?? '');
    if (array_key_exists($status, $summary)) {
        $summary[$status]++;
    }
    $category = trim((string) ($ticket['category'] ?? ''));
    $priority = fixit_priority_label((string) ($ticket['priority'] ?? ''));
    if ($category !== '') {
        $byCategory[$category] = ($byCategory[$category] ?? 0) + 1;
    }
    if ($priority !== 'Indisponível') {
        $byPriority[$priority] = ($byPriority[$priority] ?? 0) + 1;
    }
}
$headerActive = 'summary';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Consulte o resumo dos atendimentos do FixIT.">
    <title>Resumo dos atendimentos — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
<?php require __DIR__ . '/../components/header.php'; ?>
<?php if ($adminAccessDenied): ?>
<main class="app-content"><div class="page-heading"><h1>Acesso não autorizado</h1><p>Seu perfil não tem permissão para acessar a administração.</p></div><a class="button button--primary" href="<?= fixit_e($pageBase) ?>/login.php">Voltar ao login</a></main>
<?php else: ?>
<main class="app-content admin-content">
    <div class="page-heading"><h1>Resumo dos atendimentos</h1><p>Visão geral de todos os chamados da empresa.</p></div>
    <section class="admin-summary-grid" aria-label="Indicadores dos chamados">
        <?php foreach (['total' => 'Total', 'aberto' => 'Abertos', 'em_atendimento' => 'Em atendimento', 'encerrado' => 'Encerrados'] as $key => $label): ?>
            <article class="summary-card"><strong><?= $summary[$key] ?></strong><span><?= fixit_e($label) ?></span></article>
        <?php endforeach; ?>
    </section>
    <section class="distribution-grid" aria-label="Distribuição dos chamados">
        <article class="surface-card distribution-card"><h2>Chamados por categoria</h2><?php if ($byCategory === []): ?><p class="form-help">Nenhum chamado para resumir.</p><?php else: ?><dl><?php foreach ($byCategory as $label => $count): ?><div><dt><?= fixit_e($label) ?></dt><dd><?= $count ?></dd></div><?php endforeach; ?></dl><?php endif; ?></article>
        <article class="surface-card distribution-card"><h2>Chamados por prioridade</h2><?php if ($byPriority === []): ?><p class="form-help">Nenhum chamado para resumir.</p><?php else: ?><dl><?php foreach ($byPriority as $label => $count): ?><div><dt><?= fixit_e($label) ?></dt><dd><?= $count ?></dd></div><?php endforeach; ?></dl><?php endif; ?></article>
    </section>
    <a class="button button--primary" href="<?= fixit_e($pageBase) ?>/chamados.php">Ver todos os chamados</a>
</main>
<?php endif; ?>
</body>
</html>
