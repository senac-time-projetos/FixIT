<?php
declare(strict_types=1);

namespace FixIT\Controllers;

use DomainException;
use FixIT\Models\CategoryRepository;
use FixIT\Models\UserRepository;
use FixIT\Services\AdministrationService;

final class AdministrationController
{
    public function __construct(
        private readonly AdministrationService $service,
        private readonly UserRepository $users,
        private readonly CategoryRepository $categories
    ) {
    }

    public function users(array $actor): array
    {
        $this->requireAdministrator($actor);
        return array_map($this->mapUser(...), $this->users->list());
    }

    public function user(array $actor, int $id): ?array
    {
        $this->requireAdministrator($actor);
        $user = $this->users->findById($id);
        return $user === null ? null : $this->mapUser($user);
    }

    public function saveUser(array $actor, ?int $id, array $data): int
    {
        $this->requireAdministrator($actor);
        if ($id === null) {
            return $this->service->createUser($data);
        }
        if ($id === (int) $actor['id'] && ($data['status'] ?? '') === 'inativo') {
            throw new DomainException('Você não pode desativar a própria conta.');
        }
        $this->service->updateUser($id, $data);
        return $id;
    }

    public function deactivateUser(array $actor, int $id): void
    {
        $this->requireAdministrator($actor);
        $this->service->deactivateUser($id, (int) $actor['id']);
    }

    public function categories(array $actor): array
    {
        $this->requireAdministrator($actor);
        return array_map($this->mapCategory(...), $this->categories->list());
    }

    public function category(array $actor, int $id): ?array
    {
        $this->requireAdministrator($actor);
        $category = $this->categories->findById($id);
        return $category === null ? null : $this->mapCategory($category);
    }

    public function saveCategory(array $actor, ?int $id, array $data): int
    {
        $this->requireAdministrator($actor);
        if ($id === null) {
            return $this->service->createCategory($data);
        }
        $this->service->updateCategory($id, $data);
        return $id;
    }

    public function deactivateCategory(array $actor, int $id): void
    {
        $this->requireAdministrator($actor);
        $this->service->deactivateCategory($id);
    }

    private function requireAdministrator(array $actor): void
    {
        if (($actor['perfil'] ?? '') !== 'administrador') {
            throw new DomainException('Operação não permitida.');
        }
    }

    private function mapUser(array $user): array
    {
        return ['id' => (int) $user['id'], 'name' => (string) $user['nome'], 'email' => (string) $user['email'], 'role' => (string) $user['perfil'], 'status' => (string) $user['situacao']];
    }

    private function mapCategory(array $category): array
    {
        return ['id' => (int) $category['id'], 'name' => (string) $category['nome'], 'description' => (string) ($category['descricao'] ?? ''), 'status' => (string) $category['situacao']];
    }
}
