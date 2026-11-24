<?php
declare(strict_types=1);

namespace FixIT\Models;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $statement->execute(['email' => mb_strtolower(trim($email), 'UTF-8')]);
        return $statement->fetch() ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function list(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $where = [];
        $params = [];
        if (in_array($filters['perfil'] ?? null, ['colaborador', 'tecnico', 'administrador'], true)) {
            $where[] = 'perfil = :perfil';
            $params['perfil'] = $filters['perfil'];
        }
        if (in_array($filters['situacao'] ?? null, ['ativo', 'inativo'], true)) {
            $where[] = 'situacao = :situacao';
            $params['situacao'] = $filters['situacao'];
        }
        if (trim((string) ($filters['busca'] ?? '')) !== '') {
            $where[] = '(LOWER(nome) LIKE :busca_nome OR LOWER(email) LIKE :busca_email)';
            $search = '%' . mb_strtolower(mb_substr(trim((string) $filters['busca']), 0, 120), 'UTF-8') . '%';
            $params['busca_nome'] = $search;
            $params['busca_email'] = $search;
        }
        $sql = 'SELECT * FROM usuarios' . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where)) . ' ORDER BY nome, id LIMIT :limit OFFSET :offset';
        $statement = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', max(1, min($limit, 200)), PDO::PARAM_INT);
        $statement->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    /** @param array{nome:string,email:string,senha_hash:string,perfil:string,situacao?:string} $data */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO usuarios (nome, email, senha_hash, perfil, situacao) VALUES (:nome, :email, :senha_hash, :perfil, :situacao)';
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql .= ' RETURNING id';
        }
        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'nome' => trim($data['nome']),
            'email' => mb_strtolower(trim($data['email']), 'UTF-8'),
            'senha_hash' => $data['senha_hash'],
            'perfil' => $data['perfil'],
            'situacao' => $data['situacao'] ?? 'ativo',
        ]);
        return $this->insertedId($statement);
    }

    /** @param array{nome:string,email:string,perfil:string,situacao:string,senha_hash?:string|null} $data */
    public function update(int $id, array $data): bool
    {
        $fields = 'nome = :nome, email = :email, perfil = :perfil, situacao = :situacao';
        $params = [
            'id' => $id,
            'nome' => trim($data['nome']),
            'email' => mb_strtolower(trim($data['email']), 'UTF-8'),
            'perfil' => $data['perfil'],
            'situacao' => $data['situacao'],
        ];
        if (!empty($data['senha_hash'])) {
            $fields .= ', senha_hash = :senha_hash';
            $params['senha_hash'] = $data['senha_hash'];
        }
        $statement = $this->pdo->prepare('UPDATE usuarios SET ' . $fields . ' WHERE id = :id');
        $statement->execute($params);
        return $statement->rowCount() > 0;
    }

    public function deactivate(int $id): bool
    {
        $statement = $this->pdo->prepare("UPDATE usuarios SET situacao = 'inativo' WHERE id = :id AND situacao <> 'inativo'");
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
