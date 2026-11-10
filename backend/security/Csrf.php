<?php
declare(strict_types=1);

namespace FixIT\Security;

final class Csrf
{
    /** @var array<string, mixed> */
    private array $session;

    /** @param array<string, mixed> $session */
    public function __construct(array &$session)
    {
        $this->session =& $session;
    }

    public function token(): string
    {
        if (!isset($this->session['_csrf']) || !is_string($this->session['_csrf'])) {
            $this->session['_csrf'] = bin2hex(random_bytes(32));
        }
        return $this->session['_csrf'];
    }

    public function validate(?string $token): bool
    {
        $stored = $this->session['_csrf'] ?? null;
        return is_string($stored) && is_string($token) && $token !== '' && hash_equals($stored, $token);
    }

    public function rotate(): string
    {
        $this->session['_csrf'] = bin2hex(random_bytes(32));
        return $this->session['_csrf'];
    }
}
