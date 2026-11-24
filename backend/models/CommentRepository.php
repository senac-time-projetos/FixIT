<?php
declare(strict_types=1);

namespace FixIT\Models;

use PDO;

final class CommentRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(int $ticketNumber, int $authorId, string $content): int
    {
        $sql = 'INSERT INTO comentarios (chamado_numero, autor_id, conteudo) VALUES (:chamado_numero, :autor_id, :conteudo)';
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql .= ' RETURNING id';
        }
        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'chamado_numero' => $ticketNumber,
            'autor_id' => $authorId,
            'conteudo' => trim($content),
        ]);
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql'
            ? (int) $statement->fetchColumn()
            : (int) $this->pdo->lastInsertId();
    }

    /** @return list<array<string, mixed>> */
    public function listForTicket(int $ticketNumber): array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.id, c.chamado_numero, c.conteudo, c.criado_em, c.autor_id, u.nome AS autor FROM comentarios c INNER JOIN usuarios u ON u.id = c.autor_id WHERE c.chamado_numero = :chamado_numero ORDER BY c.criado_em, c.id'
        );
        $statement->execute(['chamado_numero' => $ticketNumber]);
        return $statement->fetchAll();
    }
}
