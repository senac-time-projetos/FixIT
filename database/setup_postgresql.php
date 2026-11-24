<?php
declare(strict_types=1);

use FixIT\Config\PdoConnection;

require_once dirname(__DIR__) . '/backend/config/PdoConnection.php';

$databaseUrl = getenv('DATABASE_URL');
if (!is_string($databaseUrl) || trim($databaseUrl) === '') {
    fwrite(STDERR, "Defina DATABASE_URL antes de executar o provisionamento.\n");
    exit(1);
}

try {
    $pdo = PdoConnection::connect(['url' => $databaseUrl]);
    $pdo->exec((string) file_get_contents(__DIR__ . '/postgresql.sql'));
    $pdo->exec((string) file_get_contents(__DIR__ . '/seeds/demo.postgresql.sql'));
    $admin = $pdo->query("SELECT email, perfil, situacao FROM usuarios WHERE email = 'admin@fixit.test'")->fetch();
    if (!is_array($admin) || $admin['perfil'] !== 'administrador' || $admin['situacao'] !== 'ativo') {
        throw new RuntimeException('A conta administrativa não foi provisionada corretamente.');
    }
    echo "OK: schema PostgreSQL e seed acadêmico aplicados; administrador ativo confirmado.\n";
} catch (Throwable) {
    fwrite(STDERR, "Falha ao provisionar PostgreSQL. Confira DATABASE_URL, TLS e permissões sem divulgar a credencial.\n");
    exit(1);
}
