<?php
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$email = '';
$errors = [];
$authError = false;

if ($requestMethod === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '') {
        $errors['email'] = 'Informe seu e-mail.';
    } elseif (strlen($email) > 180 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Informe um e-mail válido.';
    }

    if ($password === '') {
        $errors['password'] = 'Informe sua senha.';
    }

    // A autenticação real pertence à Issue de backend. Até a integração, um POST
    // válido demonstra somente o estado de credenciais inválidas definido no Figma.
    if ($errors === []) {
        $authError = true;
    }
}

$hasError = $errors !== [] || $authError;
$pageTitle = $hasError ? 'Confira seus dados' : 'Acesse sua conta';
$pageDescription = $hasError
    ? 'E-mail ou senha inválidos. Tente novamente.'
    : 'Conectando pessoas às soluções de TI.';
$firstInvalidField = array_key_first($errors);
$assetBase = '..';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acesse o sistema de suporte de TI FixIT.">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — FixIT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="fixit-page login-page">
    <?php require __DIR__ . '/../components/header.php'; ?>

    <main class="login-content">
        <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="login-content__description<?= $hasError ? ' login-content__description--error' : '' ?>"<?= $hasError ? ' role="alert"' : '' ?>>
            <?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <form class="login-card" method="post" action="login.php" novalidate>
            <div class="form-field">
                <label for="email">E-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    maxlength="180"
                    autocomplete="email"
                    inputmode="email"
                    placeholder="mariana@fixit.com.br"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>
                    <?= $firstInvalidField === 'email' ? 'autofocus' : '' ?>
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <p class="form-field__error" id="email-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="password">Senha</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    <?= isset($errors['password']) ? 'aria-invalid="true" aria-describedby="password-error"' : '' ?>
                    <?= $firstInvalidField === 'password' ? 'autofocus' : '' ?>
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <p class="form-field__error" id="password-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <button class="button button--primary button--full" type="submit">Entrar</button>

            <p class="login-card__help">Precisa de acesso? Procure o administrador.</p>
        </form>
    </main>
</body>
</html>
