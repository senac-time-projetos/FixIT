<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/security/Csrf.php';
require_once __DIR__ . '/../../backend/security/NativeSession.php';
require_once __DIR__ . '/../../backend/security/Authorization.php';
require_once __DIR__ . '/../../backend/services/AuthService.php';

use FixIT\Security\Authorization;
use FixIT\Security\Csrf;
use FixIT\Security\NativeSession;

$skipAuth = (bool) ($skipAuth ?? PHP_SAPI === 'cli');
$allowedRoles = is_array($allowedRoles ?? null) ? $allowedRoles : ['colaborador', 'tecnico', 'administrador'];

if ($skipAuth) {
    $logoutCsrfToken = (string) ($logoutCsrfToken ?? 'fixture-token');
    return;
}

NativeSession::start();
$session =& $_SESSION;
$authUser = is_array($session['auth_user'] ?? null) ? $session['auth_user'] : null;
if ($authUser === null) {
    header('Location: login.php');
    exit;
}
if (!Authorization::allows($authUser, $allowedRoles)) {
    http_response_code(403);
    exit('Acesso não autorizado.');
}

$currentUserName = (string) $authUser['nome'];
$currentUserId = (int) $authUser['id'];
$currentUserRole = (string) $authUser['perfil'];
$headerNavItems = match ($currentUserRole) {
    'administrador' => [
        ['key' => 'tickets', 'label' => 'Chamados', 'path' => '/pages/chamados.php'],
        ['key' => 'summary', 'label' => 'Resumo', 'path' => '/pages/resumo.php'],
        ['key' => 'users', 'label' => 'Usuários', 'path' => '/pages/usuarios.php'],
        ['key' => 'categories', 'label' => 'Categorias', 'path' => '/pages/categorias.php'],
    ],
    'tecnico' => [['key' => 'tickets', 'label' => 'Chamados', 'path' => '/pages/chamados.php']],
    default => [['key' => 'tickets', 'label' => 'Meus chamados', 'path' => '/pages/meus-chamados.php']],
};
$csrf = new Csrf($session);
$logoutCsrfToken = $csrf->token();
