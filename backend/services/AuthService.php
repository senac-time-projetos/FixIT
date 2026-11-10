<?php
declare(strict_types=1);

namespace FixIT\Services;

use FixIT\Models\UserRepository;

final class AuthService
{
    /** @var array<string, mixed> */
    private array $session;

    /** @param array<string, mixed> $session */
    public function __construct(
        private readonly UserRepository $users,
        array &$session,
        private readonly mixed $regenerateSession = null
    ) {
        $this->session =& $session;
    }

    /** @return array{id:int,nome:string,email:string,perfil:string}|null */
    public function login(string $email, string $password): ?array
    {
        $user = $this->users->findByEmail($email);
        if ($user === null || ($user['situacao'] ?? '') !== 'ativo') {
            return null;
        }
        $hash = (string) ($user['senha_hash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return null;
        }

        if (is_callable($this->regenerateSession)) {
            ($this->regenerateSession)();
        }
        $authenticated = [
            'id' => (int) $user['id'],
            'nome' => (string) $user['nome'],
            'email' => (string) $user['email'],
            'perfil' => (string) $user['perfil'],
        ];
        $this->session['auth_user'] = $authenticated;
        return $authenticated;
    }

    /** @return array{id:int,nome:string,email:string,perfil:string}|null */
    public function user(): ?array
    {
        $user = $this->session['auth_user'] ?? null;
        if (!is_array($user) || !isset($user['id'], $user['nome'], $user['email'], $user['perfil'])) {
            return null;
        }
        return $user;
    }

    public function logout(): void
    {
        unset($this->session['auth_user']);
    }

    /** @param array<string, mixed> $user */
    public static function destination(array $user): string
    {
        return match ($user['perfil'] ?? '') {
            'colaborador' => 'meus-chamados.php',
            'tecnico' => 'chamados.php',
            'administrador' => 'resumo.php',
            default => 'login.php',
        };
    }
}
