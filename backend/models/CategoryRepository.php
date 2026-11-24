<?php
declare(strict_types=1);

namespace FixIT\Models;

use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM categorias WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findByName(string $name): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM categorias WHERE LOWER(nome) = :nome LIMIT 1');
        $statement->execute(['nome' => mb_strtolower(trim($name), 'UTF-8')]);
        return $statement->fetch() ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function list(bool $onlyActive = false): array
    {
        $sql = 'SELECT * FROM categorias' . ($onlyActive ? " WHERE situacao = 'ativo'" : '') . ' ORDER BY nome, id';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(string $name, ?string $description = null): int
    {
        $sql = "INSERT INTO categorias (nome, descricao, situacao) VALUES (:nome, :descricao, 'ativo')";
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql .= ' RETURNING id';
        }
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['nome' => trim($name), 'descricao' => $description === null ? null : trim($description)]);
        return $this->insertedId($statement);
    }

    public function update(int $id, string $name, ?string $description, string $status): bool
    {
        $statement = $this->pdo->prepare('UPDATE categorias SET nome = :nome, descricao = :descricao, situacao = :situacao WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'nome' => trim($name),
            'descricao' => $description === null ? null : trim($description),
            'situacao' => $status,
        ]);
        return $statement->rowCount() > 0;
    }

    public function deactivate(int $id): bool
    {
        $statement = $this->pdo->prepare("UPDATE categorias SET situacao = 'inativo' WHERE id = :id AND situacao <> 'inativo'");
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    private function insertedId(\PDOStatement $statement): int
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql'
            ? (int) $statement->fetchColumn()
            : (int) $this->pdo->lastInsertId();
    }
}
