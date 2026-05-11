<?php
/**
 * Header compartido - Proyecto Bee
 * Requiere: Session.php
 *
 * Variables esperadas:
 *   $pageTitle - titulo de la pagina actual
 */

if (class_exists('Session')) {
    $session = Session::getInstance();
    $headerUser = $session->getUser();
}
?>
<header class="page-header">
    <div style="display:flex;align-items:center;gap:var(--spacing-sm)">
        <button class="menu-toggle" aria-label="Menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <div class="breadcrumb">
            <a href="<?php echo BASE_URL; ?>/modules/dashboard/index.php">Inicio</a>
            <?php if (isset($currentModule) && $currentModule !== 'dashboard'): ?>
                <span class="separator">/</span>
                <span class="current"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : ''; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-actions">
        <span class="header-time"></span>
        <?php if ($headerUser): ?>
        <span style="font-size:var(--font-size-sm);color:var(--color-muted)">
            <?php echo htmlspecialchars($headerUser['username']); ?>
        </span>
        <?php endif; ?>
    </div>
</header>
