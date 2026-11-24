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
        $statement = $this->pdo->prepare('SELECT * FROM categorias WHERE nome = :nome LIMIT 1');
        $statement->execute(['nome' => trim($name)]);
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
        $statement = $this->pdo->prepare("INSERT INTO categorias (nome, descricao, situacao) VALUES (:nome, :descricao, 'ativo')");
        $statement->execute(['nome' => trim($name), 'descricao' => $description === null ? null : trim($description)]);
        return (int) $this->pdo->lastInsertId();
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
}
