<?php
declare(strict_types=1);

require_once __DIR__ . '/PdoConnection.php';

use FixIT\Config\PdoConnection;

$databaseUrl = getenv('DATABASE_URL');
if (is_string($databaseUrl) && trim($databaseUrl) !== '') {
    return PdoConnection::connect(['url' => $databaseUrl]);
}

$configFile = __DIR__ . '/database.php';
if (!is_file($configFile)) {
    throw new RuntimeException(
        'Configuração do banco ausente. Copie database.example.php para database.php.'
    );
}

return PdoConnection::connect(require $configFile);
