<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
$allowedRoles = ['tecnico'];
require __DIR__ . '/../components/session_context.php';

$currentUserName = trim((string) ($currentUserName ?? 'Técnico'));
$currentTechnicianId = $currentTechnicianId ?? $currentUserId ?? null;
$ticket = is_array($ticket ?? null) ? $ticket : null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = (string) ($_POST['action'] ?? '');
$update = trim((string) ($_POST['update'] ?? ''));
$solution = trim((string) ($_POST['solution'] ?? ''));
$updateError = '';
$solutionError = '';
$serverError = '';

$isOpen = $ticket !== null && ($ticket['status'] ?? '') === 'aberto' && empty($ticket['assignee_id']) && empty($ticket['assignee']);
$isAssigned = $ticket !== null && ($ticket['status'] ?? '') === 'em_atendimento' && (string) ($ticket['assignee_id'] ?? '') === (string) $currentTechnicianId;
$isAvailable = $isOpen || $isAssigned;

if ($requestMethod === 'POST' && $isAvailable) {
    if ($action === 'assume' && $isOpen) {
        $serverError = 'Não foi possível assumir o chamado sem a integração com o servidor. Tente novamente mais tarde.';
    } elseif ($action === 'update' && $isAssigned) {
        $length = mb_strlen($update);
        if ($length < 2 || $length > 1000) {
            $updateError = 'Escreva uma atualização entre 2 e 1000 caracteres.';
        } else {
            $serverError = 'Não foi possível registrar a atualização sem a integração com o servidor. Tente novamente mais tarde.';
        }
    } elseif ($action === 'close' && $isAssigned) {
        $length = mb_strlen($solution);
        if ($length < 10 || $length > 2000) {
            $solutionError = 'Escreva uma solução entre 10 e 2000 caracteres.';
        } else {
            $serverError = 'Não foi possível encerrar o chamado sem a integração com o servidor. Tente novamente mais tarde.';
        }
    }
}

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
    <meta name="description" content="Atenda um chamado no FixIT.">
    <title><?= $ticket === null ? 'Atendimento indisponível' : 'Chamado #' . fixit_e($ticket['number'] ?? '') ?> — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
    <?php require __DIR__ . '/../components/header.php'; ?>
    <main class="app-content technician-content<?= $isAssigned ? ' technician-content--service' : '' ?>">
        <?php if (!$isAvailable): ?>
            <div class="page-heading"><h1>Atendimento indisponível</h1><p>O chamado não existe, já foi encerrado ou está atribuído a outro técnico.</p></div>
            <a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/chamados.php">Voltar para chamados</a>
        <?php elseif ($isOpen): ?>
            <div class="page-heading"><h1>Atender chamado #<?= fixit_e($ticket['number'] ?? '') ?></h1><p>Confira o problema antes de assumir o atendimento.</p></div>
            <span class="status-badge status-badge--aberto">Aberto</span>
            <section class="surface-card detail-card" aria-labelledby="ticket-title">
                <h2 id="ticket-title"><?= fixit_e($ticket['title'] ?? '') ?></h2>
                <p class="detail-card__meta"><?= fixit_e($ticket['requester'] ?? '') ?> • <?= fixit_e($ticket['department'] ?? '') ?> • <?= fixit_e($ticket['category'] ?? '') ?> • Prioridade <?= mb_strtolower(fixit_e(fixit_priority_label((string) ($ticket['priority'] ?? ''))), 'UTF-8') ?></p>
                <p><?= nl2br(fixit_e($ticket['description'] ?? '')) ?></p>
                <p class="detail-card__assignee"><strong>Responsável:</strong> nenhum técnico atribuído</p>
                <?php if ($serverError !== ''): ?><p class="message message--error" role="alert"><?= fixit_e($serverError) ?></p><?php endif; ?>
                <form method="post" action="<?= fixit_e($pageBase) ?>/atender-chamado.php?id=<?= rawurlencode((string) ($ticket['id'] ?? '')) ?>">
                    <button class="button button--primary" type="submit" name="action" value="assume">Assumir chamado</button>
                </form>
            </section>
            <p class="history-summary"><strong>Histórico:</strong> <?= fixit_e(($ticket['history'][0]['date'] ?? '')) ?> • <?= fixit_e(($ticket['history'][0]['text'] ?? 'Chamado aberto.')) ?></p>
            <a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/chamados.php">Voltar para chamados</a>
        <?php else: ?>
            <div class="page-heading"><h1>Atendimento do chamado #<?= fixit_e($ticket['number'] ?? '') ?></h1><p>Chamado atribuído a você. Registre o atendimento e a solução.</p></div>
            <span class="status-badge status-badge--em_atendimento">Em atendimento</span>
            <section class="surface-card service-card" aria-labelledby="ticket-title">
                <h2 id="ticket-title"><?= fixit_e($ticket['title'] ?? '') ?></h2>
                <p class="detail-card__meta"><strong>Responsável:</strong> <?= fixit_e($ticket['assignee'] ?? '') ?> • <?= fixit_e($ticket['category'] ?? '') ?> • Prioridade <?= mb_strtolower(fixit_e(fixit_priority_label((string) ($ticket['priority'] ?? ''))), 'UTF-8') ?></p>
                <?php if ($serverError !== ''): ?><p class="message message--error" role="alert"><?= fixit_e($serverError) ?></p><?php endif; ?>
                <form class="service-form" method="post" action="<?= fixit_e($pageBase) ?>/atender-chamado.php?id=<?= rawurlencode((string) ($ticket['id'] ?? '')) ?>" novalidate>
                    <div class="form-field"><label for="update">Atualização para o solicitante</label><textarea id="update" name="update" maxlength="1000" rows="3" placeholder="Descreva o andamento do atendimento"<?= $updateError !== '' ? ' aria-invalid="true" aria-describedby="update-error" autofocus' : '' ?>><?= fixit_e($update) ?></textarea><?php if ($updateError !== ''): ?><p class="form-field__error" id="update-error"><?= fixit_e($updateError) ?></p><?php endif; ?></div>
                    <button class="button button--primary" type="submit" name="action" value="update">Registrar atualização</button>
                    <div class="form-field"><label for="solution">Solução *</label><textarea id="solution" name="solution" maxlength="2000" rows="4" placeholder="Descreva a solução aplicada"<?= $solutionError !== '' ? ' aria-invalid="true" aria-describedby="solution-help solution-error" autofocus' : ' aria-describedby="solution-help"' ?>><?= fixit_e($solution) ?></textarea><p class="form-help" id="solution-help">Para encerrar, é obrigatório registrar uma solução.</p><?php if ($solutionError !== ''): ?><p class="form-field__error" id="solution-error"><?= fixit_e($solutionError) ?></p><?php endif; ?></div>
                    <div class="form-actions"><button class="button button--primary" type="submit" name="action" value="close">Registrar solução e encerrar</button><a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/chamados.php">Voltar</a></div>
                </form>
            </section>
            <p class="history-summary"><strong>Histórico:</strong> <?= fixit_e(($ticket['history'][0]['date'] ?? '')) ?> • <?= fixit_e(($ticket['history'][0]['text'] ?? 'Chamado aberto.')) ?></p>
        <?php endif; ?>
    </main>
</body>
</html>
