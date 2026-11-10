<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;
use FixIT\Models\UserRepository;
use FixIT\Security\Authorization;
use FixIT\Security\Csrf;
use FixIT\Services\AuthService;

$root = dirname(__DIR__);
require_once $root . '/backend/config/PdoConnection.php';
require_once $root . '/backend/models/UserRepository.php';
require_once $root . '/backend/security/Csrf.php';
require_once $root . '/backend/security/Authorization.php';
require_once $root . '/backend/services/AuthService.php';

function authAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$pdo = PdoConnection::connect(['driver' => 'sqlite', 'database' => ':memory:']);
$pdo->exec("CREATE TABLE usuarios (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, email TEXT NOT NULL UNIQUE, senha_hash TEXT NOT NULL, perfil TEXT NOT NULL, situacao TEXT NOT NULL, criado_em TEXT DEFAULT CURRENT_TIMESTAMP, atualizado_em TEXT DEFAULT CURRENT_TIMESTAMP)");
$users = new UserRepository($pdo);
$password = 'Senha-Segura-2026';
foreach (['colaborador', 'tecnico', 'administrador'] as $role) {
    $users->create([
        'nome' => ucfirst($role), 'email' => $role . '@fixit.test',
        'senha_hash' => password_hash($password, PASSWORD_DEFAULT), 'perfil' => $role,
    ]);
}
$users->create([
    'nome' => 'Inativo', 'email' => 'inativo@fixit.test',
    'senha_hash' => password_hash($password, PASSWORD_DEFAULT), 'perfil' => 'colaborador', 'situacao' => 'inativo',
]);

$session = [];
$regenerations = 0;
$auth = new AuthService($users, $session, static function () use (&$regenerations): void {
    $regenerations++;
});

authAssert($auth->login('colaborador@fixit.test', 'errada') === null, 'senha inválida precisa falhar');
authAssert($auth->login('inativo@fixit.test', $password) === null, 'conta inativa precisa falhar');
authAssert($session === [], 'falhas não podem criar sessão');

$collaborator = $auth->login('COLABORADOR@FIXIT.TEST', $password);
authAssert($collaborator !== null && $collaborator['perfil'] === 'colaborador', 'colaborador ativo precisa entrar');
authAssert($regenerations === 1, 'login precisa regenerar a sessão');
authAssert(!isset($session['auth_user']['senha_hash']) && !isset($session['auth_user']['password']), 'sessão não pode guardar segredo');
authAssert(AuthService::destination($collaborator) === 'meus-chamados.php', 'colaborador precisa do destino correto');
authAssert(AuthService::destination(['perfil' => 'tecnico']) === 'chamados.php', 'técnico precisa do destino correto');
authAssert(AuthService::destination(['perfil' => 'administrador']) === 'resumo.php', 'administrador precisa do destino correto');

authAssert(Authorization::allows($auth->user(), ['colaborador']), 'guarda precisa autorizar o perfil correto');
authAssert(!Authorization::allows($auth->user(), ['administrador']), 'guarda precisa negar outro perfil');
try {
    Authorization::requireRoles($auth->user(), ['administrador']);
    authAssert(false, 'perfil inadequado deveria lançar 403');
} catch (RuntimeException $exception) {
    authAssert($exception->getCode() === 403, 'perfil inadequado precisa usar 403');
}

$csrf = new Csrf($session);
$token = $csrf->token();
authAssert(strlen($token) === 64 && $csrf->validate($token), 'CSRF precisa gerar e validar token forte');
$newToken = $csrf->rotate();
authAssert($newToken !== $token && !$csrf->validate($token), 'rotação precisa invalidar token anterior');

$auth->logout();
authAssert($auth->user() === null, 'logout precisa remover o usuário da sessão');

echo "OK: autenticação, sessão lógica, CSRF e permissões validados.\n";
