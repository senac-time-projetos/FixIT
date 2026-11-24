<?php
declare(strict_types=1);

namespace FixIT\Models;

use PDO;

final class TicketRepository
{
    private const SELECT_DETAIL = <<<'SQL'
        SELECT c.numero AS id, c.numero, c.titulo, c.descricao, c.prioridade, c.status,
               c.solucao, c.aberto_em, c.atualizado_em, c.encerrado_em,
               c.solicitante_id, solicitante.nome AS solicitante,
               c.tecnico_id, tecnico.nome AS tecnico,
               c.categoria_id, categoria.nome AS categoria
        FROM chamados c
        INNER JOIN usuarios solicitante ON solicitante.id = c.solicitante_id
        INNER JOIN categorias categoria ON categoria.id = c.categoria_id
        LEFT JOIN usuarios tecnico ON tecnico.id = c.tecnico_id
        SQL;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findByNumber(int $number): ?array
    {
        $statement = $this->pdo->prepare(self::SELECT_DETAIL . ' WHERE c.numero = :numero LIMIT 1');
        $statement->execute(['numero' => $number]);
        return $statement->fetch() ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function listForRequester(int $requesterId, array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $filters['solicitante_id'] = $requesterId;
        return $this->listWithFilters($filters, $limit, $offset);
    }

    /** @return list<array<string, mixed>> */
    public function listQueue(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        return $this->listWithFilters($filters, $limit, $offset);
    }

    /** @param array{solicitante_id:int,categoria_id:int,titulo:string,descricao:string,prioridade:string} $data */
    public function create(array $data): int
    {
        $sql = "INSERT INTO chamados (solicitante_id, categoria_id, titulo, descricao, prioridade, status) VALUES (:solicitante_id, :categoria_id, :titulo, :descricao, :prioridade, 'aberto')";
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql .= ' RETURNING numero';
        }
        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'solicitante_id' => $data['solicitante_id'],
            'categoria_id' => $data['categoria_id'],
            'titulo' => trim($data['titulo']),
            'descricao' => trim($data['descricao']),
            'prioridade' => $data['prioridade'],
        ]);
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql'
            ? (int) $statement->fetchColumn()
            : (int) $this->pdo->lastInsertId();
    }

    public function assign(int $number, int $technicianId): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE chamados SET tecnico_id = :tecnico_id, status = 'em_atendimento' WHERE numero = :numero AND status = 'aberto' AND tecnico_id IS NULL"
        );
        $statement->execute(['numero' => $number, 'tecnico_id' => $technicianId]);
        return $statement->rowCount() === 1;
    }

    public function close(int $number, int $technicianId, string $solution): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE chamados SET status = 'encerrado', solucao = :solucao, encerrado_em = CURRENT_TIMESTAMP WHERE numero = :numero AND status = 'em_atendimento' AND tecnico_id = :tecnico_id"
        );
        $statement->execute([
            'numero' => $number,
            'tecnico_id' => $technicianId,
            'solucao' => trim($solution),
        ]);
        return $statement->rowCount() === 1;
    }

    /** @return array{total:int,aberto:int,em_atendimento:int,encerrado:int,por_categoria:list<array<string,mixed>>,por_prioridade:list<array<string,mixed>>} */
    public function summary(): array
    {
        $statuses = ['total' => 0, 'aberto' => 0, 'em_atendimento' => 0, 'encerrado' => 0];
        $rows = $this->pdo->query('SELECT status, COUNT(*) AS quantidade FROM chamados GROUP BY status')->fetchAll();
        foreach ($rows as $row) {
            $status = (string) $row['status'];
            $quantity = (int) $row['quantidade'];
            if (array_key_exists($status, $statuses)) {
                $statuses[$status] = $quantity;
                $statuses['total'] += $quantity;
            }
        }

        $byCategory = $this->pdo->query(
            'SELECT categoria.nome, COUNT(*) AS quantidade FROM chamados INNER JOIN categorias categoria ON categoria.id = chamados.categoria_id GROUP BY categoria.id, categoria.nome ORDER BY categoria.nome'
        )->fetchAll();
        $byPriority = $this->pdo->query(
            'SELECT prioridade, COUNT(*) AS quantidade FROM chamados GROUP BY prioridade ORDER BY prioridade'
        )->fetchAll();

        return $statuses + ['por_categoria' => $byCategory, 'por_prioridade' => $byPriority];
    }

    /** @return list<array<string, mixed>> */
    private function listWithFilters(array $filters, int $limit, int $offset): array
    {
        $where = [];
        $params = [];
        if (isset($filters['solicitante_id'])) {
            $where[] = 'c.solicitante_id = :solicitante_id';
            $params['solicitante_id'] = (int) $filters['solicitante_id'];
        }
        if (in_array($filters['status'] ?? null, ['aberto', 'em_atendimento', 'encerrado'], true)) {
            $where[] = 'c.status = :status';
            $params['status'] = $filters['status'];
        }
        if (in_array($filters['prioridade'] ?? null, ['baixa', 'media', 'alta'], true)) {
            $where[] = 'c.prioridade = :prioridade';
            $params['prioridade'] = $filters['prioridade'];
        }
        if (isset($filters['categoria_id']) && (int) $filters['categoria_id'] > 0) {
            $where[] = 'c.categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }
        $search = mb_substr(trim((string) ($filters['busca'] ?? '')), 0, 120);
        if ($search !== '') {
            $like = '%' . mb_strtolower($search, 'UTF-8') . '%';
            if (ctype_digit($search)) {
                $where[] = '(c.numero = :numero_busca OR LOWER(c.titulo) LIKE :busca_titulo)';
                $params['numero_busca'] = (int) $search;
                $params['busca_titulo'] = $like;
            } else {
                $where[] = '(LOWER(c.titulo) LIKE :busca_titulo OR LOWER(solicitante.nome) LIKE :busca_solicitante)';
                $params['busca_titulo'] = $like;
                $params['busca_solicitante'] = $like;
            }
        }

        $sql = self::SELECT_DETAIL
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
            . ' ORDER BY c.aberto_em DESC, c.numero DESC LIMIT :limit OFFSET :offset';
        $statement = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', max(1, min($limit, 200)), PDO::PARAM_INT);
        $statement->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
