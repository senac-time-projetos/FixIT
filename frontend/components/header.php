<?php
/**
 * Cabeçalho compartilhado da aplicação.
 *
 * A página que inclui este componente pode informar $assetBase para ajustar os
 * caminhos relativos. Em frontend/pages/, o padrão ".." aponta para frontend/.
 */
$assetBase = $assetBase ?? '..';
?>
<header class="app-header">
    <a class="app-brand" href="<?= htmlspecialchars($assetBase . '/index.php', ENT_QUOTES, 'UTF-8') ?>" aria-label="FixIT — página inicial">
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
</header>
