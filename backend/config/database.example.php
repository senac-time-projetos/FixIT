<?php
/**
 * Exemplo de configuração de banco para ambiente local com XAMPP.
 *
 * 1. Copie este arquivo para: database.php
 * 2. Ajuste os valores para o banco da equipe.
 * 3. NÃO versione database.php com credenciais reais.
 */

$env = static function (string $key, string $default): string {
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
};

return [
    'driver' => 'mysql',
    'host' => $env('FIXIT_DB_HOST', '127.0.0.1'),
    'port' => $env('FIXIT_DB_PORT', '3306'),
    'database' => $env('FIXIT_DB_NAME', 'projeto_integrador'),
    'username' => $env('FIXIT_DB_USER', 'root'),
    'password' => $env('FIXIT_DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
];
