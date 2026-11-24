<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;

$root = dirname(__DIR__);
require_once $root . '/backend/config/PdoConnection.php';
$databaseUrl = getenv('DATABASE_URL');
if (!is_string($databaseUrl) || trim($databaseUrl) === '') {
    echo "SKIP: DATABASE_URL não definida; validação PostgreSQL hospedada não executada.\n";
    exit(0);
}

function hostedPostgresqlAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

try {
    $pdo = PdoConnection::connect(['url' => $databaseUrl]);
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'")->fetchAll(PDO::FETCH_COLUMN);
    foreach (['usuarios', 'categorias', 'chamados', 'comentarios', 'historico_chamados'] as $table) {
        hostedPostgresqlAssert(in_array($table, $tables, true), 'base hospedada precisa conter ' . $table);
    }
    $statement = $pdo->prepare('SELECT senha_hash, perfil, situacao FROM usuarios WHERE email = :email');
    $statement->execute(['email' => 'admin@fixit.test']);
    $admin = $statement->fetch();
    hostedPostgresqlAssert(is_array($admin) && $admin['perfil'] === 'administrador' && $admin['situacao'] === 'ativo', 'administrador precisa estar ativo');
    hostedPostgresqlAssert((password_get_info((string) $admin['senha_hash'])['algoName'] ?? 'unknown') !== 'unknown', 'administrador precisa armazenar hash reconhecido pelo PHP');
    echo "OK: PostgreSQL hospedado, cinco tabelas e administrador validados.\n";
} catch (Throwable) {
    fwrite(STDERR, "FALHA: conexão ou estrutura PostgreSQL indisponível; a credencial não foi exibida.\n");
    exit(1);
}
