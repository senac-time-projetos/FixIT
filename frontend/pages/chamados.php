<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
$allowedRoles = ['tecnico', 'administrador'];
require __DIR__ . '/../components/session_context.php';

$currentUserName = trim((string) ($currentUserName ?? 'Técnico'));
$tickets = is_array($tickets ?? null) ? $tickets : [];
$categories = is_array($categories ?? null) ? $categories : [];
$query = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 120);
$statusFilter = (string) ($_GET['status'] ?? 'todos');
$priorityFilter = (string) ($_GET['priority'] ?? 'todas');
$categoryFilter = (string) ($_GET['category'] ?? 'todas');

if (!in_array($statusFilter, ['todos', 'aberto', 'em_atendimento', 'encerrado'], true)) {
    $statusFilter = 'todos';
}
if (!in_array($priorityFilter, ['todas', 'baixa', 'media', 'alta'], true)) {
    $priorityFilter = 'todas';
}
if ($categoryFilter !== 'todas' && !array_key_exists($categoryFilter, $categories)) {
    $categoryFilter = 'todas';
}

$visibleTickets = array_values(array_filter($tickets, static function (array $ticket) use ($query, $statusFilter, $priorityFilter, $categoryFilter): bool {
    if ($statusFilter !== 'todos' && ($ticket['status'] ?? '') !== $statusFilter) {
        return false;
    }
    if ($priorityFilter !== 'todas' && ($ticket['priority'] ?? '') !== $priorityFilter) {
        return false;
    }
    $ticketCategoryKey = (string) ($ticket['category_key'] ?? '');
    if ($categoryFilter !== 'todas' && $ticketCategoryKey !== $categoryFilter) {
        return false;
    }
    if ($query === '') {
        return true;
    }
    $haystack = implode(' ', [(string) ($ticket['number'] ?? ''), (string) ($ticket['title'] ?? ''), (string) ($ticket['requester'] ?? '')]);
    return mb_stripos($haystack, $query) !== false;
}));

$assetBase = $assetBase ?? '..';
$pageBase = $pageBase ?? '.';
$headerActive = 'tickets';
$headerNavLabel = 'Chamados';
$headerNavPath = '/pages/chamados.php';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Organize e acompanhe a fila de chamados do FixIT.">
    <title>Chamados — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
    <?php require __DIR__ . '/../components/header.php'; ?>
    <main class="app-content technician-content">
        <div class="page-heading">
            <h1>Chamados</h1>
            <p>Organize a fila e assuma os atendimentos.</p>
        </div>

        <form class="filter-bar filter-bar--technician" method="get" action="<?= fixit_e($pageBase) ?>/chamados.php" role="search">
            <div class="form-field form-field--compact">
                <label for="q">Buscar</label>
                <input id="q" name="q" type="search" maxlength="120" placeholder="Título, número ou solicitante" value="<?= fixit_e($query) ?>">
            </div>
            <div class="form-field form-field--compact">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach (['todos' => 'Todos', 'aberto' => 'Aberto', 'em_atendimento' => 'Em atendimento', 'encerrado' => 'Encerrado'] as $value => $label): ?>
                        <option value="<?= fixit_e($value) ?>"<?= $statusFilter === $value ? ' selected' : '' ?>><?= fixit_e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field form-field--compact">
                <label for="priority">Prioridade</label>
                <select id="priority" name="priority">
                    <?php foreach (['todas' => 'Todas', 'baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'] as $value => $label): ?>
                        <option value="<?= fixit_e($value) ?>"<?= $priorityFilter === $value ? ' selected' : '' ?>><?= fixit_e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field form-field--compact">
                <label for="category">Categoria</label>
                <select id="category" name="category">
                    <option value="todas">Todas</option>
                    <?php foreach ($categories as $value => $label): ?><option value="<?= fixit_e($value) ?>"<?= $categoryFilter === (string) $value ? ' selected' : '' ?>><?= fixit_e($label) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="filter-bar__actions">
                <button class="button button--primary" type="submit">Aplicar filtros</button>
                <a class="button button--primary" href="<?= fixit_e($pageBase) ?>/novo-chamado.php">+ Novo chamado</a>
            </div>
        </form>

        <?php if ($visibleTickets === []): ?>
            <section class="empty-state" aria-labelledby="empty-title">
                <h2 id="empty-title">Nenhum chamado encontrado</h2>
                <p><?= $tickets === [] ? 'A fila está vazia no momento.' : 'Tente alterar a busca ou os filtros.' ?></p>
            </section>
        <?php else: ?>
            <div class="ticket-table-wrap">
                <table class="ticket-table ticket-table--technician">
                    <thead><tr><th>Chamado</th><th>Solicitante</th><th>Prioridade</th><th>Responsável / status</th><th>Ação</th></tr></thead>
                    <tbody>
                    <?php foreach ($visibleTickets as $ticket):
                        $canAssume = ($ticket['status'] ?? '') === 'aberto' && empty($ticket['assignee']);
                        $actionLabel = $canAssume ? 'Assumir' : (($ticket['status'] ?? '') === 'encerrado' ? 'Ver solução' : 'Ver detalhes');
                        $actionPage = $canAssume || ($ticket['status'] ?? '') === 'em_atendimento' ? 'atender-chamado.php' : 'chamado.php';
                    ?>
                        <tr>
                            <td><strong>#<?= fixit_e($ticket['number'] ?? '') ?></strong><br><?= fixit_e($ticket['title'] ?? '') ?></td>
                            <td><?= fixit_e($ticket['requester'] ?? '') ?></td>
                            <td><?= fixit_e(fixit_priority_label((string) ($ticket['priority'] ?? ''))) ?></td>
                            <td><?= !empty($ticket['assignee']) ? fixit_e($ticket['assignee']) : 'Sem técnico' ?><br><span class="status-inline status-inline--<?= fixit_e($ticket['status'] ?? '') ?>"><?= fixit_e(fixit_status_label((string) ($ticket['status'] ?? ''))) ?></span></td>
                            <td><a href="<?= fixit_e($pageBase . '/' . $actionPage) ?>?id=<?= rawurlencode((string) ($ticket['id'] ?? '')) ?>"><?= fixit_e($actionLabel) ?> →</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <section class="ticket-cards" aria-label="Chamados">
                <?php foreach ($visibleTickets as $ticket): $canAssume = ($ticket['status'] ?? '') === 'aberto' && empty($ticket['assignee']); ?>
                    <article class="ticket-card">
                        <span class="status-badge status-badge--<?= fixit_e($ticket['status'] ?? '') ?>"><?= fixit_e(fixit_status_label((string) ($ticket['status'] ?? ''))) ?></span>
                        <h2>#<?= fixit_e($ticket['number'] ?? '') ?> • <?= fixit_e($ticket['title'] ?? '') ?></h2>
                        <p><?= fixit_e($ticket['requester'] ?? '') ?> • Prioridade <?= mb_strtolower(fixit_e(fixit_priority_label((string) ($ticket['priority'] ?? ''))), 'UTF-8') ?></p>
                        <a class="button button--primary" href="<?= fixit_e($pageBase) ?>/<?= $canAssume || ($ticket['status'] ?? '') === 'em_atendimento' ? 'atender-chamado.php' : 'chamado.php' ?>?id=<?= rawurlencode((string) ($ticket['id'] ?? '')) ?>"><?= $canAssume ? 'Assumir chamado' : 'Ver detalhes' ?></a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
