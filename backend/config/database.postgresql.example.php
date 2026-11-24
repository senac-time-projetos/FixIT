<?php
declare(strict_types=1);

/**
 * Alternativa local quando o servidor não injeta DATABASE_URL diretamente.
 * Copie para database.php e defina DATABASE_URL fora do Git.
 */
$databaseUrl = getenv('DATABASE_URL');
if (!is_string($databaseUrl) || trim($databaseUrl) === '') {
    throw new RuntimeException('Defina DATABASE_URL no ambiente.');
}

return ['url' => $databaseUrl];
