<?php
declare(strict_types=1);

/**
 * Configuração local do MySQL iniciado pelo compose.yaml.
 * Copie para database.php; esse destino é ignorado pelo Git.
 */
return [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3307',
    'database' => 'projeto_integrador',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];
