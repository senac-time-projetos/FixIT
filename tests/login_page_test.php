<?php
declare(strict_types=1);

function renderLogin(string $method, array $post = []): string
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_POST = $post;

    ob_start();
    include __DIR__ . '/../frontend/pages/login.php';
    return (string) ob_get_clean();
}

function assertContains(string $needle, string $haystack, string $message): void
{
    if (!str_contains($haystack, $needle)) {
        fwrite(STDERR, "Falha: {$message}\n");
        exit(1);
    }
}

function assertNotContains(string $needle, string $haystack, string $message): void
{
    if (str_contains($haystack, $needle)) {
        fwrite(STDERR, "Falha: {$message}\n");
        exit(1);
    }
}

$getHtml = renderLogin('GET');
assertContains('<h1>Acesse sua conta</h1>', $getHtml, 'GET deve exibir o título normal.');
assertContains('assets/logos/fixit-logo.png', $getHtml, 'A marca deve usar o asset local.');
assertContains('for="email"', $getHtml, 'O campo de e-mail deve possuir label.');
assertContains('for="password"', $getHtml, 'O campo de senha deve possuir label.');
assertNotContains('Demonstração:', $getHtml, 'Controles de protótipo não pertencem ao produto final.');
preg_match('/name="_csrf" value="([a-f0-9]{64})"/', $getHtml, $csrfMatch);
$csrfToken = $csrfMatch[1] ?? '';
assertContains('name="_csrf"', $getHtml, 'O login precisa de proteção CSRF.');

$invalidHtml = renderLogin('POST', ['email' => 'invalido', 'password' => '']);
assertContains('Informe um e-mail válido.', $invalidHtml, 'E-mail inválido deve ser informado.');
assertContains('Informe sua senha.', $invalidHtml, 'Senha obrigatória deve ser informada.');
assertContains('aria-invalid="true"', $invalidHtml, 'Campos inválidos devem ser anunciados.');

$authHtml = renderLogin('POST', ['_csrf' => $csrfToken, 'email' => 'mariana@fixit.com.br', 'password' => 'segredo-local']);
assertContains('Confira seus dados', $authHtml, 'POST válido deve preparar o estado inválido do Figma.');
assertContains('E-mail ou senha inválidos. Tente novamente.', $authHtml, 'Erro de autenticação deve ser genérico.');
assertNotContains('segredo-local', $authHtml, 'A senha nunca deve voltar no HTML.');

fwrite(STDOUT, "OK: tela de Login validada.\n");
