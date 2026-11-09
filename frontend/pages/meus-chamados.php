<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';

$currentUserName = trim((string) ($currentUserName ?? 'Colaborador'));
$tickets = is_array($tickets ?? null) ? $tickets : [];
$query = trim((string) ($_GET['q'] ?? ''));
$query = mb_substr($query, 0, 120);
$statusFilter = (string) ($_GET['status'] ?? 'todos');
$allowedStatuses = ['todos', 'aberto', 'em_atendimento', 'encerrado'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'todos';
}

$visibleTickets = array_values(array_filter($tickets, static function (array $ticket) use ($query, $statusFilter): bool {
    $status = (string) ($ticket['status'] ?? '');
    if ($statusFilter !== 'todos' && $status !== $statusFilter) {
        return false;
    }

    if ($query === '') {
        return true;
    }

    $haystack = (string) ($ticket['number'] ?? '') . ' ' . (string) ($ticket['title'] ?? '');
    return mb_stripos($haystack, $query) !== false;
}));

$summary = ['aberto' => 0, 'em_atendimento' => 0, 'encerrado' => 0];
foreach ($tickets as $ticket) {
    $status = (string) ($ticket['status'] ?? '');
    if (array_key_exists($status, $summary)) {
        $summary[$status]++;
    }
}

$assetBase = $assetBase ?? '..';
$pageBase = $pageBase ?? '.';
$headerActive = 'tickets';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Consulte e acompanhe seus chamados no FixIT.">
    <title>Meus chamados — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
    <?php require __DIR__ . '/../components/header.php'; ?>

    <main class="app-content">
        <div class="page-heading">
            <h1>Meus chamados</h1>
            <p>Abra uma solicitação e acompanhe o atendimento.</p>
        </div>

        <a class="button button--primary" href="<?= fixit_e($pageBase) ?>/novo-chamado.php">+ Novo chamado</a>

        <section class="summary-grid" aria-label="Resumo dos chamados">
            <article class="summary-card"><strong><?= $summary['aberto'] ?></strong><span>Abertos</span></article>
            <article class="summary-card"><strong><?= $summary['em_atendimento'] ?></strong><span>Em atendimento</span></article>
            <article class="summary-card"><strong><?= $summary['encerrado'] ?></strong><span>Encerrados</span></article>
        </section>

        <form class="filter-bar" method="get" action="<?= fixit_e($pageBase) ?>/meus-chamados.php" role="search">
            <div class="form-field form-field--compact">
                <label for="q">Buscar chamado</label>
                <input id="q" name="q" type="search" maxlength="120" placeholder="Título ou número" value="<?= fixit_e($query) ?>">
            </div>
            <div class="form-field form-field--compact">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="todos"<?= $statusFilter === 'todos' ? ' selected' : '' ?>>Todos</option>
                    <option value="aberto"<?= $statusFilter === 'aberto' ? ' selected' : '' ?>>Aberto</option>
                    <option value="em_atendimento"<?= $statusFilter === 'em_atendimento' ? ' selected' : '' ?>>Em atendimento</option>
                    <option value="encerrado"<?= $statusFilter === 'encerrado' ? ' selected' : '' ?>>Encerrado</option>
                </select>
            </div>
            <button class="button button--primary filter-bar__submit" type="submit">Buscar</button>
        </form>

        <?php if ($visibleTickets === []): ?>
            <section class="empty-state" aria-labelledby="empty-title">
                <h2 id="empty-title">Nenhum chamado encontrado</h2>
                <p><?= $tickets === [] ? 'Quando você abrir uma solicitação, ela aparecerá aqui.' : 'Tente alterar a busca ou o filtro de status.' ?></p>
                <a class="button button--primary" href="<?= fixit_e($pageBase) ?>/novo-chamado.php">Abrir novo chamado</a>
            </section>
        <?php else: ?>
            <div class="ticket-table-wrap">
                <table class="ticket-table">
                    <thead><tr><th>Número / título</th><th>Categoria</th><th>Prioridade</th><th>Status</th><th>Ação</th></tr></thead>
                    <tbody>
                    <?php foreach ($visibleTickets as $ticket): ?>
                        <tr>
                            <td>#<?= fixit_e($ticket['number'] ?? '') ?> • <?= fixit_e($ticket['title'] ?? '') ?></td>
                            <td><?= fixit_e($ticket['category'] ?? '') ?></td>
                            <td><?= fixit_e(fixit_priority_label((string) ($ticket['priority'] ?? ''))) ?></td>
                            <td><?= fixit_e(fixit_status_label((string) ($ticket['status'] ?? ''))) ?></td>
                            <td><a href="<?= fixit_e($pageBase) ?>/chamado.php?id=<?= rawurlencode((string) ($ticket['id'] ?? '')) ?>"><?= ($ticket['status'] ?? '') === 'encerrado' ? 'Ver solução' : 'Ver chamado' ?> →</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <section class="ticket-cards" aria-label="Chamados">
                <?php foreach ($visibleTickets as $ticket): ?>
                    <article class="ticket-card">
                        <span class="status-badge status-badge--<?= fixit_e($ticket['status'] ?? 'indisponivel') ?>"><?= fixit_e(fixit_status_label((string) ($ticket['status'] ?? ''))) ?></span>
                        <h2>#<?= fixit_e($ticket['number'] ?? '') ?> • <?= fixit_e($ticket['title'] ?? '') ?></h2>
                        <p><?= fixit_e($ticket['category'] ?? '') ?> • Prioridade <?= mb_strtolower(fixit_e(fixit_priority_label((string) ($ticket['priority'] ?? ''))), 'UTF-8') ?></p>
                        <a class="button button--primary" href="<?= fixit_e($pageBase) ?>/chamado.php?id=<?= rawurlencode((string) ($ticket['id'] ?? '')) ?>">Ver chamado</a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
