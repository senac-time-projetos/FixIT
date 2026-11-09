<?php
declare(strict_types=1);

if (!function_exists('fixit_e')) {
    function fixit_e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('fixit_status_label')) {
    function fixit_status_label(string $status): string
    {
        return [
            'aberto' => 'Aberto',
            'em_atendimento' => 'Em atendimento',
            'encerrado' => 'Encerrado',
        ][$status] ?? 'Status indisponível';
    }
}

if (!function_exists('fixit_priority_label')) {
    function fixit_priority_label(string $priority): string
    {
        return [
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
        ][$priority] ?? 'Indisponível';
    }
}
