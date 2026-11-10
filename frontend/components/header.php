<?php
/**
 * Cabeçalho compartilhado da aplicação.
 *
 * A página que inclui este componente pode informar $assetBase para ajustar os
 * caminhos relativos. Em frontend/pages/, o padrão ".." aponta para frontend/.
 */
$assetBase = $assetBase ?? '..';
$currentUserName = trim((string) ($currentUserName ?? ''));
$headerActive = (string) ($headerActive ?? '');
$isAuthenticated = $currentUserName !== '';
$headerNavLabel = trim((string) ($headerNavLabel ?? 'Meus chamados'));
$headerNavPath = (string) ($headerNavPath ?? '/pages/meus-chamados.php');
$headerNavItems = is_array($headerNavItems ?? null) ? $headerNavItems : [[
    'key' => 'tickets',
    'label' => $headerNavLabel,
    'path' => $headerNavPath,
]];
$firstNavPath = (string) ($headerNavItems[0]['path'] ?? $headerNavPath);
$homeUrl = $isAuthenticated ? $assetBase . $firstNavPath : $assetBase . '/index.php';
$logoutCsrfToken = (string) ($logoutCsrfToken ?? '');
?>
<header class="app-header<?= $isAuthenticated ? ' app-header--authenticated' : '' ?>">
    <a class="app-brand" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="FixIT — página inicial">
        <span class="app-brand__crop" aria-hidden="true">
            <img
                class="app-brand__image"
                src="<?= htmlspecialchars($assetBase . '/assets/logos/fixit-logo.png', ENT_QUOTES, 'UTF-8') ?>"
                alt=""
                width="1536"
                height="1024"
            >
        </span>
    </a>
    <p class="app-header__context">Suporte de TI</p>
    <?php if ($isAuthenticated): ?>
        <nav class="app-header__nav" aria-label="Navegação principal">
            <?php foreach ($headerNavItems as $item): ?>
                <a class="button <?= $headerActive === ($item['key'] ?? '') ? 'button--primary is-active' : 'button--secondary' ?>" href="<?= htmlspecialchars($assetBase . (string) ($item['path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="app-header__account">
            <span class="app-header__user"><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></span>
            <form class="app-header__logout" method="post" action="<?= htmlspecialchars($assetBase . '/pages/logout.php', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($logoutCsrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button class="button button--primary" type="submit">Sair</button>
            </form>
        </div>
    <?php endif; ?>
</header>
