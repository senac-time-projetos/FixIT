<?php
declare(strict_types=1);

namespace FixIT\Controllers;

use DomainException;
use FixIT\Models\CategoryRepository;
use FixIT\Models\CommentRepository;
use FixIT\Models\HistoryRepository;
use FixIT\Models\TicketRepository;
use FixIT\Services\TicketService;

final class TechnicianTicketController
{
    public function __construct(
        private readonly TicketService $service,
        private readonly TicketRepository $tickets,
        private readonly CategoryRepository $categories,
        private readonly CommentRepository $comments,
        private readonly HistoryRepository $history
    ) {
    }

    /** @return array<int, string> */
    public function categories(bool $onlyActive = false): array
    {
        $result = [];
        foreach ($this->categories->list($onlyActive) as $category) {
            $result[(int) $category['id']] = (string) $category['nome'];
        }
        return $result;
    }

    /** @param array{id:int,perfil:string} $actor @return list<array<string, mixed>> */
    public function queue(array $actor, array $filters = []): array
    {
        $this->requireRole($actor, ['tecnico', 'administrador']);
        return array_map($this->mapTicket(...), $this->tickets->listQueue($filters));
    }

    /** @param array{id:int,nome:string,perfil:string} $actor */
    public function open(array $actor, array $input): int
    {
        $this->requireRole($actor, ['tecnico']);
        return $this->service->open($actor, [
            'titulo' => (string) ($input['title'] ?? ''),
            'descricao' => (string) ($input['description'] ?? ''),
            'categoria_id' => (int) ($input['category'] ?? 0),
            'prioridade' => (string) ($input['priority'] ?? ''),
        ]);
    }

    /** @param array{id:int,perfil:string} $actor @return array<string, mixed>|null */
    public function detail(array $actor, int $number): ?array
    {
        $this->requireRole($actor, ['tecnico', 'administrador']);
        if ($number < 1) {
            return null;
        }
        $ticket = $this->tickets->findByNumber($number);
        if ($ticket === null || !$this->service->canView($actor, $ticket)) {
            return null;
        }
        $view = $this->mapTicket($ticket);
        $timeline = [];
        foreach ($this->history->listForTicket($number) as $event) {
            $timeline[] = [
                'date' => $this->formatDate((string) $event['criado_em']),
                'text' => (string) $event['descricao'],
                'sort' => (string) $event['criado_em'] . '-0-' . str_pad((string) $event['id'], 10, '0', STR_PAD_LEFT),
            ];
        }
        foreach ($this->comments->listForTicket($number) as $comment) {
            $timeline[] = [
                'date' => $this->formatDate((string) $comment['criado_em']),
                'text' => (string) $comment['autor'] . ': ' . (string) $comment['conteudo'],
                'sort' => (string) $comment['criado_em'] . '-1-' . str_pad((string) $comment['id'], 10, '0', STR_PAD_LEFT),
            ];
        }
        usort($timeline, static fn (array $left, array $right): int => $left['sort'] <=> $right['sort']);
        $view['history'] = array_map(static fn (array $item): array => ['date' => $item['date'], 'text' => $item['text']], $timeline);
        return $view;
    }

    /** @param array{id:int,nome:string,perfil:string} $actor */
    public function assign(array $actor, int $number): void
    {
        $this->requireRole($actor, ['tecnico']);
        $this->service->assign($number, $actor);
    }

    /** @param array{id:int,nome:string,perfil:string} $actor */
    public function update(array $actor, int $number, string $content): int
    {
        $this->requireRole($actor, ['tecnico']);
        return $this->service->registerUpdate($number, $actor, $content);
    }

    /** @param array{id:int,nome:string,perfil:string} $actor */
    public function close(array $actor, int $number, string $solution): void
    {
        $this->requireRole($actor, ['tecnico']);
        $this->service->close($number, $actor, $solution);
    }

    /** @return array<string, mixed> */
    private function mapTicket(array $ticket): array
    {
        return [
            'id' => (int) $ticket['numero'],
            'number' => (int) $ticket['numero'],
            'title' => (string) $ticket['titulo'],
            'description' => (string) $ticket['descricao'],
            'category' => (string) $ticket['categoria'],
            'category_key' => (string) $ticket['categoria_id'],
            'priority' => (string) $ticket['prioridade'],
            'status' => (string) $ticket['status'],
            'requester' => (string) $ticket['solicitante'],
            'assignee' => $ticket['tecnico'] === null ? null : (string) $ticket['tecnico'],
            'assignee_id' => $ticket['tecnico_id'] === null ? null : (int) $ticket['tecnico_id'],
            'solution' => $ticket['solucao'] === null ? null : (string) $ticket['solucao'],
            'resolved_by' => $ticket['tecnico'] === null ? null : (string) $ticket['tecnico'],
            'resolved_at' => empty($ticket['encerrado_em']) ? null : $this->formatDate((string) $ticket['encerrado_em']),
        ];
    }

    /** @param array{perfil?:string} $actor @param list<string> $roles */
    private function requireRole(array $actor, array $roles): void
    {
        if (!in_array($actor['perfil'] ?? '', $roles, true)) {
            throw new DomainException('Operação não permitida.');
        }
    }

    private function formatDate(string $value): string
    {
        $timestamp = strtotime($value);
        return $timestamp === false ? $value : date('d/m/Y H:i', $timestamp);
    }
}
