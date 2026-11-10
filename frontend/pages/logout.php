<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/security/Csrf.php';
require_once __DIR__ . '/../../backend/security/NativeSession.php';

use FixIT\Security\Csrf;
use FixIT\Security\NativeSession;

NativeSession::start();
$session =& $_SESSION;
$csrf = new Csrf($session);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !$csrf->validate((string) ($_POST['_csrf'] ?? ''))) {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

unset($session['auth_user']);
NativeSession::destroy();
header('Location: login.php');
exit;
