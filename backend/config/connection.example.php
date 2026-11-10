<?php
declare(strict_types=1);

/**
 * Exemplo de bootstrap da conexão. A aplicação deve incluir connection.php,
 * que possui o mesmo contrato e lê database.php (arquivo local ignorado).
 */

require_once __DIR__ . '/PdoConnection.php';

use FixIT\Config\PdoConnection;

return PdoConnection::connect(require __DIR__ . '/database.php');
