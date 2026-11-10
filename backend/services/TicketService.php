<?php
declare(strict_types=1);

namespace FixIT\Services;

use DomainException;
use FixIT\Config\PdoConnection;
use FixIT\Models\CategoryRepository;
use FixIT\Models\CommentRepository;
use FixIT\Models\HistoryRepository;
use FixIT\Models\TicketRepository;
use PDO;

final class TicketService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly TicketRepository $tickets,
        private readonly CategoryRepository $categories,
        private readonly CommentRepository $comments,
        private readonly HistoryRepository $history
    ) {
    }

    /** @param array{id:int,nome:string,perfil:string} $actor */
    public function open(array $actor, array $input): int
    {
        $this->requireRole($actor, ['colaborador', 'tecnico']);
        $title = trim((string) ($input['titulo'] ?? ''));
        $description = trim((string) ($input['descricao'] ?? ''));
        $categoryId = (int) ($input['categoria_id'] ?? 0);
        $priority = (string) ($input['prioridade'] ?? '');
        if (mb_strlen($title) < 3 || mb_strlen($title) > 160) {
            throw new DomainException('Título deve ter entre 3 e 160 caracteres.');
        }
        if (mb_strlen($description) < 10 || mb_strlen($description) > 5000) {
            throw new DomainException('Descrição deve ter entre 10 e 5000 caracteres.');
        }
        if (!in_array($priority, ['baixa', 'media', 'alta'], true)) {
            throw new DomainException('Prioridade inválida.');
        }
        $category = $this->categories->findById($categoryId);
        if ($category === null || ($category['situacao'] ?? '') !== 'ativo') {
            throw new DomainException('Categoria indisponível.');
        }

        return PdoConnection::transaction($this->pdo, function () use ($actor, $categoryId, $title, $description, $priority): int {
            $number = $this->tickets->create([
                'solicitante_id' => (int) $actor['id'],
                'categoria_id' => $categoryId,
                'titulo' => $title,
                'descricao' => $description,
                'prioridade' => $priority,
            ]);
            $this->history->append([
                'chamado_numero' => $number,
                'autor_id' => (int) $actor['id'],
                'evento' => 'abertura',
                'descricao' => 'Chamado aberto por ' . $actor['nome'] . '.',
                'estado_novo' => 'aberto',
            ]);
            return $number;
        });
    }

    /** @param array{id:int,perfil:string} $actor */
    public function canView(array $actor, array $ticket): bool
    {
        if (in_array($actor['perfil'] ?? '', ['tecnico', 'administrador'], true)) {
            return true;
        }
        return ($actor['perfil'] ?? '') === 'colaborador'
            && (int) ($ticket['solicitante_id'] ?? 0) === (int) $actor['id'];
    }

    /** @param array{id:int,nome:string,perfil:string} $actor */
    public function assign(int $number, array $actor): void
    {
        $this->requireRole($actor, ['tecnico']);
        PdoConnection::transaction($this->pdo, function () use ($number, $actor): void {
            if (!$this->tickets->assign($number, (int) $actor['id'])) {
                throw new DomainException('Chamado não está disponível para atribuição.');
            }
            $this->history->append([
                'chamado_numero' => $number, 'autor_id' => (int) $actor['id'],
                'evento' => 'atribuicao', 'descricao' => $actor['nome'] . ' assumiu o atendimento.',
                'estado_anterior' => 'aberto', 'estado_novo' => 'em_atendimento',
            ]);
        });
    }

    /** @param array{id:int,perfil:string} $actor */
    public function comment(int $number, array $actor, string $content): int
    {
        $content = trim($content);
        if (mb_strlen($content) < 2 || mb_strlen($content) > 1000) {
            throw new DomainException('Comentário deve ter entre 2 e 1000 caracteres.');
        }
        $ticket = $this->requireTicket($number);
        if (!$this->canView($actor, $ticket)) {
            throw new DomainException('Chamado indisponível.');
        }
        return $this->comments->create($number, (int) $actor['id'], $content);
    }

    /** @param array{id:int,nome:string,perfil:string} $actor */
    public function registerUpdate(int $number, array $actor, string $content): int
    {
        $content = trim($content);
        if (mb_strlen($content) < 2 || mb_strlen($content) > 1000) {
            throw new DomainException('Atualização deve ter entre 2 e 1000 caracteres.');
        }
        $ticket = $this->requireAssignedTicket($number, $actor);
        return $this->history->append([
            'chamado_numero' => (int) $ticket['numero'], 'autor_id' => (int) $actor['id'],
            'evento' => 'atualizacao', 'descricao' => $content,
            'estado_anterior' => 'em_atendimento', 'estado_novo' => 'em_atendimento',
        ]);
    }

    /** @param array{id:int,nome:string,perfil:string} $actor */
    public function close(int $number, array $actor, string $solution): void
    {
        $solution = trim($solution);
        if (mb_strlen($solution) < 10 || mb_strlen($solution) > 2000) {
            throw new DomainException('Solução deve ter entre 10 e 2000 caracteres.');
        }
        $this->requireAssignedTicket($number, $actor);
        PdoConnection::transaction($this->pdo, function () use ($number, $actor, $solution): void {
            if (!$this->tickets->close($number, (int) $actor['id'], $solution)) {
                throw new DomainException('Chamado não está disponível para encerramento.');
            }
            $this->history->append([
                'chamado_numero' => $number, 'autor_id' => (int) $actor['id'],
                'evento' => 'encerramento', 'descricao' => 'Chamado encerrado com solução registrada.',
                'estado_anterior' => 'em_atendimento', 'estado_novo' => 'encerrado',
            ]);
        });
    }

    /** @return array<string, mixed> */
    private function requireTicket(int $number): array
    {
        $ticket = $this->tickets->findByNumber($number);
        if ($ticket === null) {
            throw new DomainException('Chamado indisponível.');
        }
        return $ticket;
    }

    /** @param array{id:int,perfil:string} $actor @return array<string, mixed> */
    private function requireAssignedTicket(int $number, array $actor): array
    {
        $this->requireRole($actor, ['tecnico']);
        $ticket = $this->requireTicket($number);
        if (($ticket['status'] ?? '') !== 'em_atendimento' || (int) ($ticket['tecnico_id'] ?? 0) !== (int) $actor['id']) {
            throw new DomainException('Chamado não está atribuído a este técnico.');
        }
        return $ticket;
    }

    private function requireRole(array $actor, array $roles): void
    {
        if (!in_array($actor['perfil'] ?? '', $roles, true)) {
            throw new DomainException('Operação não permitida.');
        }
    }
}
