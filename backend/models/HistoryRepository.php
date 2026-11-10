<?php
declare(strict_types=1);

namespace FixIT\Models;

use PDO;

final class HistoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param array{chamado_numero:int,autor_id?:int|null,evento:string,descricao:string,estado_anterior?:string|null,estado_novo?:string|null} $event */
    public function append(array $event): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO historico_chamados (chamado_numero, autor_id, evento, descricao, estado_anterior, estado_novo) VALUES (:chamado_numero, :autor_id, :evento, :descricao, :estado_anterior, :estado_novo)'
        );
        $statement->execute([
            'chamado_numero' => $event['chamado_numero'],
            'autor_id' => $event['autor_id'] ?? null,
            'evento' => $event['evento'],
            'descricao' => trim($event['descricao']),
            'estado_anterior' => $event['estado_anterior'] ?? null,
            'estado_novo' => $event['estado_novo'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array<string, mixed>> */
    public function listForTicket(int $ticketNumber): array
    {
        $statement = $this->pdo->prepare(
            'SELECT h.*, u.nome AS autor FROM historico_chamados h LEFT JOIN usuarios u ON u.id = h.autor_id WHERE h.chamado_numero = :chamado_numero ORDER BY h.criado_em, h.id'
        );
        $statement->execute(['chamado_numero' => $ticketNumber]);
        return $statement->fetchAll();
    }
}
