<?php
declare(strict_types=1);

$currentUserName = trim((string) ($currentUserName ?? 'Administrador'));
$assetBase = $assetBase ?? '..';
$pageBase = $pageBase ?? '.';
$adminAccessDenied = (bool) ($adminAccessDenied ?? false);
$headerNavItems = [
    ['key' => 'tickets', 'label' => 'Chamados', 'path' => '/pages/chamados.php'],
    ['key' => 'summary', 'label' => 'Resumo', 'path' => '/pages/resumo.php'],
    ['key' => 'users', 'label' => 'Usuários', 'path' => '/pages/usuarios.php'],
    ['key' => 'categories', 'label' => 'Categorias', 'path' => '/pages/categorias.php'],
];
