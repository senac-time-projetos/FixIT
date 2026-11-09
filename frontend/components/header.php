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
$homeUrl = $isAuthenticated ? $assetBase . '/pages/meus-chamados.php' : $assetBase . '/index.php';
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
            <a class="button button--primary<?= $headerActive === 'tickets' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($assetBase . '/pages/meus-chamados.php', ENT_QUOTES, 'UTF-8') ?>">
                Meus chamados
            </a>
        </nav>
        <div class="app-header__account">
            <span class="app-header__user"><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></span>
            <a class="button button--primary" href="<?= htmlspecialchars($assetBase . '/pages/login.php', ENT_QUOTES, 'UTF-8') ?>">Sair</a>
        </div>
    <?php endif; ?>
</header>
