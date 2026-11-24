<?php
declare(strict_types=1);

namespace FixIT\Services;

use DomainException;
use FixIT\Models\CategoryRepository;
use FixIT\Models\UserRepository;

final class AdministrationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly CategoryRepository $categories
    ) {
    }

    public function createUser(array $data): int
    {
        $data = $this->validateUser($data, false);
        if ($this->users->findByEmail($data['email']) !== null) {
            throw new DomainException('Já existe um usuário com este e-mail.');
        }
        return $this->users->create([
            'nome' => $data['name'],
            'email' => $data['email'],
            'senha_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'perfil' => $data['role'],
            'situacao' => $data['status'],
        ]);
    }

    public function updateUser(int $id, array $data): void
    {
        $existing = $this->users->findById($id);
        if ($existing === null) {
            throw new DomainException('Usuário não encontrado.');
        }
        $data = $this->validateUser($data, true);
        $duplicate = $this->users->findByEmail($data['email']);
        if ($duplicate !== null && (int) $duplicate['id'] !== $id) {
            throw new DomainException('Já existe um usuário com este e-mail.');
        }
        $this->users->update($id, [
            'nome' => $data['name'],
            'email' => $data['email'],
            'perfil' => $data['role'],
            'situacao' => $data['status'],
            'senha_hash' => $data['password'] === '' ? null : password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    public function deactivateUser(int $id, int $actorId): void
    {
        if ($id === $actorId) {
            throw new DomainException('Você não pode desativar a própria conta.');
        }
        if ($this->users->findById($id) === null) {
            throw new DomainException('Usuário não encontrado.');
        }
        $this->users->deactivate($id);
    }

    public function createCategory(array $data): int
    {
        $data = $this->validateCategory($data);
        if ($this->categories->findByName($data['name']) !== null) {
            throw new DomainException('Já existe uma categoria com este nome.');
        }
        return $this->categories->create($data['name'], $data['description'] === '' ? null : $data['description']);
    }

    public function updateCategory(int $id, array $data): void
    {
        if ($this->categories->findById($id) === null) {
            throw new DomainException('Categoria não encontrada.');
        }
        $data = $this->validateCategory($data);
        $duplicate = $this->categories->findByName($data['name']);
        if ($duplicate !== null && (int) $duplicate['id'] !== $id) {
            throw new DomainException('Já existe uma categoria com este nome.');
        }
        $this->categories->update($id, $data['name'], $data['description'] === '' ? null : $data['description'], $data['status']);
    }

    public function deactivateCategory(int $id): void
    {
        if ($this->categories->findById($id) === null) {
            throw new DomainException('Categoria não encontrada.');
        }
        $this->categories->deactivate($id);
    }

    /** @return array{name:string,email:string,role:string,status:string,password:string} */
    private function validateUser(array $data, bool $editing): array
    {
        $result = [
            'name' => trim((string) ($data['name'] ?? '')),
            'email' => mb_strtolower(trim((string) ($data['email'] ?? '')), 'UTF-8'),
            'role' => (string) ($data['role'] ?? ''),
            'status' => (string) ($data['status'] ?? ''),
            'password' => (string) ($data['password'] ?? ''),
        ];
        if (mb_strlen($result['name']) < 2 || mb_strlen($result['name']) > 120) {
            throw new DomainException('Informe um nome entre 2 e 120 caracteres.');
        }
        if (mb_strlen($result['email']) > 160 || filter_var($result['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Informe um e-mail válido de até 160 caracteres.');
        }
        if (!in_array($result['role'], ['colaborador', 'tecnico', 'administrador'], true)) {
            throw new DomainException('Selecione um perfil válido.');
        }
        if (!in_array($result['status'], ['ativo', 'inativo'], true)) {
            throw new DomainException('Selecione uma situação válida.');
        }
        $passwordLength = mb_strlen($result['password']);
        if ((!$editing && $passwordLength < 8) || ($editing && $passwordLength > 0 && $passwordLength < 8)) {
            throw new DomainException($editing ? 'A nova senha deve ter pelo menos 8 caracteres.' : 'A senha inicial deve ter pelo menos 8 caracteres.');
        }
        return $result;
    }

    /** @return array{name:string,description:string,status:string} */
    private function validateCategory(array $data): array
    {
        $result = [
            'name' => trim((string) ($data['name'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')),
            'status' => (string) ($data['status'] ?? 'ativo'),
        ];
        if (mb_strlen($result['name']) < 2 || mb_strlen($result['name']) > 80) {
            throw new DomainException('Informe um nome entre 2 e 80 caracteres.');
        }
        if (mb_strlen($result['description']) > 255) {
            throw new DomainException('A descrição deve ter no máximo 255 caracteres.');
        }
        if (!in_array($result['status'], ['ativo', 'inativo'], true)) {
            throw new DomainException('Selecione uma situação válida.');
        }
        return $result;
    }
}
