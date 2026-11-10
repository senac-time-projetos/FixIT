<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;
use FixIT\Models\UserRepository;
use FixIT\Services\AuthService;

$root = dirname(__DIR__);
require_once $root . '/backend/config/PdoConnection.php';
require_once $root . '/backend/models/UserRepository.php';
require_once $root . '/backend/services/AuthService.php';

function dockerDbAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$env = static function (string $key, string $default): string {
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
};

try {
    $pdo = PdoConnection::connect([
        'driver' => 'mysql',
        'host' => $env('FIXIT_DB_HOST', '127.0.0.1'),
        'port' => $env('FIXIT_DB_DOCKER_PORT', '3307'),
        'database' => $env('FIXIT_DB_NAME', 'projeto_integrador'),
        'username' => $env('FIXIT_DB_USER', 'root'),
        'password' => $env('FIXIT_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ]);
} catch (Throwable $exception) {
    fwrite(STDERR, "FALHA: MySQL Docker indisponível: {$exception->getMessage()}\n");
    exit(1);
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach (['usuarios', 'categorias', 'chamados', 'comentarios', 'historico_chamados'] as $table) {
    dockerDbAssert(in_array($table, $tables, true), 'instalação precisa conter a tabela ' . $table);
}
dockerDbAssert((int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() >= 3, 'seed precisa criar os três perfis fictícios');
dockerDbAssert((int) $pdo->query("SELECT COUNT(*) FROM categorias WHERE situacao = 'ativo'")->fetchColumn() >= 4, 'seed precisa criar categorias ativas');
dockerDbAssert((string) $pdo->query('SELECT @@character_set_database')->fetchColumn() === 'utf8mb4', 'banco precisa usar utf8mb4');

$session = [];
$auth = new AuthService(new UserRepository($pdo), $session);
$authenticated = $auth->login('mariana@fixit.test', 'FixIT@Colab2026');
dockerDbAssert($authenticated !== null && $authenticated['perfil'] === 'colaborador', 'autenticação real precisa validar o hash do seed');

$pdo->beginTransaction();
$pdo->exec("INSERT INTO categorias (nome, descricao, situacao) VALUES ('Teste Docker temporário', 'Registro revertido pelo teste.', 'ativo')");
$pdo->rollBack();
dockerDbAssert((int) $pdo->query("SELECT COUNT(*) FROM categorias WHERE nome = 'Teste Docker temporário'")->fetchColumn() === 0, 'rollback real precisa preservar o banco');

echo "OK: MySQL Docker saudável, schema, seed, login, utf8mb4 e transação validados.\n";
