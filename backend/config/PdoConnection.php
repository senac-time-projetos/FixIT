<?php
declare(strict_types=1);

namespace FixIT\Config;

use InvalidArgumentException;
use PDO;
use Throwable;

final class PdoConnection
{
    /** @param array<string, mixed> $config */
    public static function connect(array $config): PDO
    {
        $driver = (string) ($config['driver'] ?? 'mysql');
        if ($driver === 'sqlite') {
            $database = (string) ($config['database'] ?? ':memory:');
            $pdo = new PDO('sqlite:' . $database);
            self::configure($pdo, $driver);
            $pdo->exec('PRAGMA foreign_keys = ON');
            return $pdo;
        }

        if ($driver !== 'mysql') {
            throw new InvalidArgumentException('Driver de banco não suportado.');
        }

        foreach (['host', 'port', 'database', 'username'] as $required) {
            if (!isset($config[$required]) || trim((string) $config[$required]) === '') {
                throw new InvalidArgumentException('Configuração de banco incompleta: ' . $required . '.');
            }
        }

        $charset = (string) ($config['charset'] ?? 'utf8mb4');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            (string) $config['host'],
            (string) $config['port'],
            (string) $config['database'],
            $charset
        );

        $pdo = new PDO(
            $dsn,
            (string) $config['username'],
            (string) ($config['password'] ?? '')
        );
        self::configure($pdo, $driver);
        return $pdo;
    }

    public static function transaction(PDO $pdo, callable $operation): mixed
    {
        if ($pdo->inTransaction()) {
            return $operation($pdo);
        }

        $pdo->beginTransaction();
        try {
            $result = $operation($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    private static function configure(PDO $pdo, string $driver): void
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        if ($driver === 'mysql') {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
    }
}
