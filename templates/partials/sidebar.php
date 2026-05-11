<?php
/**
 * Sidebar de Navegacion - Proyecto Bee
 * Requiere: constants.php, Session.php, auth.functions.php
 *
 * Variables esperadas:
 *   $currentModule  - modulo activo (inventory, rbac, clients, sales, dashboard)
 *   $currentPage    - pagina activa dentro del modulo
 */

$user = null;
if (class_exists('Session')) {
    $session = Session::getInstance();
    $user = $session->getUser();
}

function sidebarIsActive($module, $page = null) {
    global $currentModule, $currentPage;
    if ($page !== null) {
        return ($currentModule === $module && $currentPage === $page) ? 'active' : '';
    }
    return ($currentModule === $module) ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <!-- Marca -->
    <div class="sidebar-brand">
        <div class="brand-logo">
            <img src="<?php echo ASSETS_URL; ?>/img/logo.jpg" alt="Bee">
        </div>
        <div>
            <div class="brand-name">Bee</div>
            <div class="brand-version">v<?php echo APP_VERSION; ?></div>
        </div>
    </div>

    <!-- Usuario -->
    <?php if ($user): ?>
    <div class="sidebar-user">
        <div class="avatar"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navegacion -->
    <nav class="sidebar-nav">
        <!-- Principal -->
        <div class="nav-section">
            <div class="nav-section-title">Principal</div>
            <a href="<?php echo BASE_URL; ?>/modules/dashboard/index.php" class="nav-link <?php echo sidebarIsActive('dashboard'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Dashboard
            </a>
        </div>

        <!-- Inventario -->
        <div class="nav-section">
            <div class="nav-section-title">Inventario</div>
            <a href="<?php echo BASE_URL; ?>/modules/inventory/stock.php" class="nav-link <?php echo sidebarIsActive('inventory', 'stock'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="2" width="20" height="8" rx="2"/>
                    <rect x="2" y="14" width="20" height="8" rx="2"/>
                    <line x1="6" y1="6" x2="6.01" y2="6"/>
                    <line x1="6" y1="18" x2="6.01" y2="18"/>
                </svg>
                Stock
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/inventory/products.php" class="nav-link <?php echo sidebarIsActive('inventory', 'products'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                Productos
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/inventory/suppliers.php" class="nav-link <?php echo sidebarIsActive('inventory', 'suppliers'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Proveedores
            </a>
        </div>

        <!-- Clientes -->
        <div class="nav-section">
            <div class="nav-section-title">Clientes</div>
            <a href="<?php echo BASE_URL; ?>/modules/clients/list.php" class="nav-link <?php echo sidebarIsActive('clients', 'list'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <polyline points="17 11 19 13 23 9"/>
                </svg>
                Clientes
            </a>
        </div>

        <!-- Ventas -->
        <div class="nav-section">
            <div class="nav-section-title">Ventas</div>
            <a href="<?php echo BASE_URL; ?>/modules/sales/pos.php" class="nav-link <?php echo sidebarIsActive('sales', 'pos'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>
                Punto de Venta
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/sales/history.php" class="nav-link <?php echo sidebarIsActive('sales', 'history'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Historial
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/sales/reports.php" class="nav-link <?php echo sidebarIsActive('sales', 'reports'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                Reportes
            </a>
        </div>

        <!-- RBAC -->
        <div class="nav-section">
            <div class="nav-section-title">Administracion</div>
            <a href="<?php echo BASE_URL; ?>/modules/rbac/roles.php" class="nav-link <?php echo sidebarIsActive('rbac', 'roles'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
                Roles
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/rbac/permissions.php" class="nav-link <?php echo sidebarIsActive('rbac', 'permissions'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                Permisos
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/rbac/users.php" class="nav-link <?php echo sidebarIsActive('rbac', 'users'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Usuarios
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/rbac/audit-log.php" class="nav-link <?php echo sidebarIsActive('rbac', 'audit'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Auditoria
            </a>
        </div>
    </nav>

    <!-- Footer sidebar -->
    <div class="sidebar-footer">
        <a href="<?php echo BASE_URL; ?>/modules/auth/logout.php" class="logout-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Cerrar Sesion
        </a>
    </div>
</aside>

<!-- Backdrop movil -->
<div class="sidebar-backdrop"></div>
