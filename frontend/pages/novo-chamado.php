<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/view_helpers.php';
$allowedRoles = ['colaborador'];
require __DIR__ . '/../components/session_context.php';

$currentUserName = trim((string) ($currentUserName ?? 'Colaborador'));
$collaboratorController = null;
$actor = ['id' => (int) ($currentUserId ?? 0), 'nome' => $currentUserName, 'perfil' => (string) ($currentUserRole ?? 'colaborador')];
if (!$skipAuth) {
    $application = require __DIR__ . '/../../backend/bootstrap.php';
    $collaboratorController = $application['collaborator_ticket_controller'];
    $categories = $collaboratorController->activeCategories();
} else {
    $categories = is_array($categories ?? null) ? $categories : [];
}
$initialForm = is_array($initialForm ?? null) ? $initialForm : [];
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$values = [
    'title' => (string) ($initialForm['title'] ?? ''),
    'category' => (string) ($initialForm['category'] ?? ''),
    'priority' => (string) ($initialForm['priority'] ?? ''),
    'description' => (string) ($initialForm['description'] ?? ''),
];
$errors = [];
$serverError = '';

if ($requestMethod === 'POST') {
    foreach (array_keys($values) as $field) {
        $values[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if (!$skipAuth && !$csrf->validate((string) ($_POST['_csrf'] ?? ''))) {
        $serverError = 'Sua sessão expirou. Atualize a página e tente novamente.';
    }

    $titleLength = mb_strlen($values['title']);
    if ($titleLength < 5 || $titleLength > 120) {
        $errors['title'] = 'Informe um título entre 5 e 120 caracteres.';
    }
    if (!array_key_exists($values['category'], $categories)) {
        $errors['category'] = 'Selecione uma categoria válida.';
    }
    if (!in_array($values['priority'], ['baixa', 'media', 'alta'], true)) {
        $errors['priority'] = 'Selecione uma prioridade válida.';
    }
    $descriptionLength = mb_strlen($values['description']);
    if ($descriptionLength < 10 || $descriptionLength > 2000) {
        $errors['description'] = 'Descreva o problema com 10 a 2000 caracteres.';
    }

    if ($errors === [] && $serverError === '') {
        if ($skipAuth) {
            $serverError = 'Não foi possível abrir o chamado sem a integração com o servidor. Tente novamente mais tarde.';
        } else {
            try {
                $number = $collaboratorController->open($actor, $values);
                header('Location: chamado.php?id=' . rawurlencode((string) $number) . '&opened=1');
                exit;
            } catch (DomainException $exception) {
                $serverError = $exception->getMessage();
            } catch (Throwable) {
                $serverError = 'Não foi possível abrir o chamado. Tente novamente mais tarde.';
            }
        }
    }
}

$hasValidationError = $errors !== [];
$firstInvalidField = array_key_first($errors);
$assetBase = $assetBase ?? '..';
$pageBase = $pageBase ?? '.';
$headerActive = 'tickets';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Abra um novo chamado de suporte no FixIT.">
    <title><?= $hasValidationError ? 'Confira os campos obrigatórios' : 'Novo chamado' ?> — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= fixit_e($assetBase) ?>/css/styles.css">
</head>
<body class="fixit-page">
    <?php require __DIR__ . '/../components/header.php'; ?>

    <main class="app-content">
        <div class="page-heading">
            <h1><?= $hasValidationError ? 'Confira os campos obrigatórios' : 'Novo chamado' ?></h1>
            <p class="<?= $hasValidationError ? 'message message--error' : '' ?>"<?= $hasValidationError ? ' role="alert"' : '' ?>>
                <?= $hasValidationError ? 'Informe o título e descreva o problema antes de abrir o chamado.' : 'Conte o que aconteceu. Os campos com * são obrigatórios.' ?>
            </p>
        </div>

        <?php if ($serverError !== ''): ?><p class="message message--error" role="alert"><?= fixit_e($serverError) ?></p><?php endif; ?>

        <form class="surface-card ticket-form" method="post" action="<?= fixit_e($pageBase) ?>/novo-chamado.php" novalidate>
            <input type="hidden" name="_csrf" value="<?= fixit_e($skipAuth ? 'fixture-token' : $csrf->token()) ?>">
            <div class="form-field">
                <label for="title">Título *</label>
                <input id="title" name="title" type="text" maxlength="120" placeholder="Informe um título para o chamado" value="<?= fixit_e($values['title']) ?>"<?= isset($errors['title']) ? ' aria-invalid="true" aria-describedby="title-error"' : '' ?><?= $firstInvalidField === 'title' ? ' autofocus' : '' ?> required>
                <?php if (isset($errors['title'])): ?><p class="form-field__error" id="title-error"><?= fixit_e($errors['title']) ?></p><?php endif; ?>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="category">Categoria *</label>
                    <select id="category" name="category"<?= isset($errors['category']) ? ' aria-invalid="true" aria-describedby="category-error"' : '' ?><?= $firstInvalidField === 'category' ? ' autofocus' : '' ?> required>
                        <option value="">Selecione</option>
                        <?php foreach ($categories as $value => $label): ?><option value="<?= fixit_e($value) ?>"<?= $values['category'] === (string) $value ? ' selected' : '' ?>><?= fixit_e($label) ?></option><?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['category'])): ?><p class="form-field__error" id="category-error"><?= fixit_e($errors['category']) ?></p><?php endif; ?>
                </div>
                <div class="form-field">
                    <label for="priority">Prioridade *</label>
                    <select id="priority" name="priority"<?= isset($errors['priority']) ? ' aria-invalid="true" aria-describedby="priority-error priority-help"' : ' aria-describedby="priority-help"' ?><?= $firstInvalidField === 'priority' ? ' autofocus' : '' ?> required>
                        <option value="">Selecione</option>
                        <option value="baixa"<?= $values['priority'] === 'baixa' ? ' selected' : '' ?>>Baixa</option>
                        <option value="media"<?= $values['priority'] === 'media' ? ' selected' : '' ?>>Média</option>
                        <option value="alta"<?= $values['priority'] === 'alta' ? ' selected' : '' ?>>Alta</option>
                    </select>
                    <?php if (isset($errors['priority'])): ?><p class="form-field__error" id="priority-error"><?= fixit_e($errors['priority']) ?></p><?php endif; ?>
                </div>
            </div>
            <p class="form-help" id="priority-help">Prioridades: baixa — não impede o trabalho; média — dificulta; alta — impede o trabalho.</p>

            <div class="form-field">
                <label for="description">Descrição *</label>
                <textarea id="description" name="description" maxlength="2000" rows="5" placeholder="Descreva o problema"<?= isset($errors['description']) ? ' aria-invalid="true" aria-describedby="description-error"' : '' ?><?= $firstInvalidField === 'description' ? ' autofocus' : '' ?> required><?= fixit_e($values['description']) ?></textarea>
                <?php if (isset($errors['description'])): ?><p class="form-field__error" id="description-error"><?= fixit_e($errors['description']) ?></p><?php endif; ?>
            </div>

            <p class="form-help">O chamado será registrado como Aberto e receberá um número para acompanhamento.</p>
            <div class="form-actions">
                <button class="button button--primary" type="submit">Abrir chamado</button>
                <a class="button button--secondary" href="<?= fixit_e($pageBase) ?>/meus-chamados.php">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>
