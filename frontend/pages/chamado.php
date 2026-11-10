<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
$allowedRoles = ['colaborador', 'tecnico', 'administrador'];
require __DIR__ . '/../components/session_context.php';

$currentUserName = trim((string) ($currentUserName ?? 'Colaborador'));
$controller = null;
$actor = ['id' => (int) ($currentUserId ?? 0), 'nome' => $currentUserName, 'perfil' => (string) ($currentUserRole ?? 'colaborador')];
if (!$skipAuth && $actor['perfil'] === 'colaborador') {
    $application = require __DIR__ . '/../../backend/bootstrap.php';
    $controller = $application['collaborator_ticket_controller'];
    $ticket = $controller->detail($actor, filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: 0);
} else {
    $ticket = is_array($ticket ?? null) ? $ticket : null;
}
$successMessage = trim((string) ($successMessage ?? ''));
if (!$skipAuth && isset($_GET['opened'])) {
    $successMessage = 'Chamado aberto com sucesso.';
} elseif (!$skipAuth && isset($_GET['commented'])) {
    $successMessage = 'Comentário enviado com sucesso.';
}
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$comment = '';
$commentError = '';
$serverError = '';
$isClosed = $ticket !== null && ($ticket['status'] ?? '') === 'encerrado';

if ($requestMethod === 'POST' && $ticket !== null && !$isClosed) {
    $comment = trim((string) ($_POST['comment'] ?? ''));
    if (!$skipAuth && !$csrf->validate((string) ($_POST['_csrf'] ?? ''))) {
        $serverError = 'Sua sessão expirou. Atualize a página e tente novamente.';
    }
    $commentLength = mb_strlen($comment);
    if ($commentLength < 2 || $commentLength > 1000) {
        $commentError = 'Escreva um comentário entre 2 e 1000 caracteres.';
    } elseif ($serverError === '') {
        if ($skipAuth) {
            $serverError = 'Não foi possível enviar o comentário sem a integração com o servidor. Tente novamente mais tarde.';
        } else {
            try {
                $controller->comment($actor, (int) $ticket['number'], $comment);
                header('Location: chamado.php?id=' . rawurlencode((string) $ticket['number']) . '&commented=1');
                exit;
            } catch (DomainException $exception) {
                $serverError = $exception->getMessage();
            } catch (Throwable) {
                $serverError = 'Não foi possível enviar o comentário. Tente novamente mais tarde.';
            }
        }
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
    <meta name="description" content="Acompanhe os detalhes do chamado no FixIT.">
    <title><?= $ticket === null ? 'Chamado indisponível' : 'Chamado #' . fixit_e($ticket['number'] ?? '') ?> — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
    <?php require __DIR__ . '/../components/header.php'; ?>

    <main class="app-content">
        <?php if ($ticket === null): ?>
            <div class="page-heading">
                <h1>Chamado indisponível</h1>
                <p>Não foi possível carregar este chamado. Ele pode não existir ou você pode não ter permissão para acessá-lo.</p>
            </div>
            <a class="button button--primary" href="<?= fixit_e($pageBase) ?>/meus-chamados.php"><span class="label-desktop">Voltar para meus chamados</span><span class="label-mobile">Voltar</span></a>
        <?php else: ?>
            <div class="page-heading">
                <h1>Chamado #<?= fixit_e($ticket['number'] ?? '') ?><?= $isClosed ? ' encerrado' : '' ?></h1>
                <p><?= $isClosed ? 'A solução permanece disponível para consulta no histórico.' : ($successMessage !== '' ? fixit_e($successMessage) : 'Acompanhe as atualizações por aqui.') ?></p>
            </div>
            <span class="status-badge status-badge--<?= fixit_e($ticket['status'] ?? 'indisponivel') ?>"><?= fixit_e(fixit_status_label((string) ($ticket['status'] ?? ''))) ?></span>
            <?php if ($successMessage !== ''): ?><p class="message message--success detail-success-mobile" role="status"><?= fixit_e($successMessage) ?></p><?php endif; ?>

            <?php if ($isClosed): ?>
                <section class="surface-card detail-card solution-card" aria-labelledby="ticket-title">
                    <h2 id="ticket-title"><?= fixit_e($ticket['title'] ?? '') ?></h2>
                    <p><?= nl2br(fixit_e($ticket['solution'] ?? 'Solução não informada.')) ?></p>
                    <p class="detail-card__meta">Resolvido por <?= fixit_e($ticket['resolved_by'] ?? $ticket['assignee'] ?? '') ?><?= !empty($ticket['resolved_at']) ? ' • ' . fixit_e($ticket['resolved_at']) : '' ?></p>
                </section>
            <?php else: ?>
                <section class="surface-card detail-card" aria-labelledby="ticket-title">
                    <h2 id="ticket-title"><?= fixit_e($ticket['title'] ?? '') ?></h2>
                    <p class="detail-card__meta"><?= fixit_e($ticket['category'] ?? '') ?> • Prioridade <?= mb_strtolower(fixit_e(fixit_priority_label((string) ($ticket['priority'] ?? ''))), 'UTF-8') ?> • Solicitante: <?= fixit_e($ticket['requester'] ?? '') ?></p>
                    <p><?= nl2br(fixit_e($ticket['description'] ?? '')) ?></p>
                    <p class="detail-card__assignee"><strong>Técnico responsável:</strong> <?= !empty($ticket['assignee']) ? fixit_e($ticket['assignee']) : 'aguardando atendimento' ?></p>
                </section>
            <?php endif; ?>

            <section class="surface-card history-card" aria-labelledby="history-title">
                <h2 id="history-title" class="visually-hidden">Comentários e histórico</h2>
                <div class="history-list">
                    <?php foreach (($ticket['history'] ?? []) as $event): ?><p><time><?= fixit_e($event['date'] ?? '') ?></time> • <?= fixit_e($event['text'] ?? '') ?></p><?php endforeach; ?>
                    <?php if (($ticket['history'] ?? []) === []): ?><p class="form-help">Ainda não há atualizações neste chamado.</p><?php endif; ?>
                </div>

                <?php if (!$isClosed): ?>
                    <?php if ($serverError !== ''): ?><p class="message message--error" role="alert"><?= fixit_e($serverError) ?></p><?php endif; ?>
                    <form method="post" action="<?= fixit_e($pageBase) ?>/chamado.php?id=<?= rawurlencode((string) ($ticket['id'] ?? '')) ?>" novalidate>
                    <input type="hidden" name="_csrf" value="<?= fixit_e($skipAuth ? 'fixture-token' : $csrf->token()) ?>">
                    <div class="form-field">
                        <label for="comment">Adicionar comentário</label>
                        <textarea id="comment" name="comment" maxlength="1000" rows="4" placeholder="Informações adicionais sobre o problema"<?= $commentError !== '' ? ' aria-invalid="true" aria-describedby="comment-error" autofocus' : '' ?>><?= fixit_e($comment) ?></textarea>
                        <?php if ($commentError !== ''): ?><p class="form-field__error" id="comment-error"><?= fixit_e($commentError) ?></p><?php endif; ?>
                    </div>
                    <button class="button button--primary" type="submit">Enviar comentário</button>
                    </form>
                <?php endif; ?>
            </section>

            <a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/meus-chamados.php"><span class="label-desktop">Voltar para meus chamados</span><span class="label-mobile">Voltar</span></a>
        <?php endif; ?>
    </main>
</body>
</html>
