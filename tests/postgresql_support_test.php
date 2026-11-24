<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;

$root = dirname(__DIR__);
require_once $root . '/backend/config/PdoConnection.php';

function postgresqlSupportAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FALHA: {$message}\n");
        exit(1);
    }
}

$config = PdoConnection::fromUrl('postgresql://usuario:senha%20segura@db.example.test:5544/fixit?sslmode=verify-full&channel_binding=require');
postgresqlSupportAssert($config['driver'] === 'pgsql', 'URL precisa selecionar pgsql');
postgresqlSupportAssert($config['port'] === '5544' && $config['database'] === 'fixit', 'URL precisa preservar porta e banco');
postgresqlSupportAssert($config['password'] === 'senha segura' && $config['sslmode'] === 'verify-full', 'URL precisa decodificar segredo e TLS');
try {
    PdoConnection::fromUrl('mysql://usuario:senha@localhost/fixit');
    postgresqlSupportAssert(false, 'URL não PostgreSQL precisa falhar');
} catch (InvalidArgumentException) {
}

$schema = file_get_contents($root . '/database/postgresql.sql');
$seed = file_get_contents($root . '/database/seeds/demo.postgresql.sql');
foreach (['usuarios', 'categorias', 'chamados', 'comentarios', 'historico_chamados'] as $table) {
    postgresqlSupportAssert(str_contains($schema, 'CREATE TABLE IF NOT EXISTS ' . $table), 'schema precisa criar ' . $table);
}
postgresqlSupportAssert(str_contains($seed, "'admin@fixit.test'"), 'seed precisa incluir administrador');
preg_match("/'Administrador FixIT', 'admin@fixit\.test', '([^']+)'/", $seed, $matches);
postgresqlSupportAssert(isset($matches[1]) && password_verify('FixIT@Admin2026', $matches[1]), 'administrador precisa usar hash bcrypt válido');
postgresqlSupportAssert(!str_contains($schema, 'DROP TABLE'), 'schema não pode remover tabelas');

echo "OK: suporte seguro a PostgreSQL validado.\n";
