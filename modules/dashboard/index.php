<?php
/**
 * Dashboard - Proyecto Bee
 * Panel principal con resumen del sistema
 */

$pageTitle = 'Dashboard';
$currentModule = 'dashboard';
$currentPage = 'index';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

// Obtener contadores
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stats = [];

    // Total productos
    $stmt = $conn->query("SELECT COUNT(*) FROM inv_products WHERE is_active = 1");
    $stats['products'] = $stmt->fetchColumn();

    // Productos con stock bajo
    $stmt = $conn->query("SELECT COUNT(*) FROM inv_products WHERE stock <= min_stock AND is_active = 1");
    $stats['low_stock'] = $stmt->fetchColumn();

    // Total proveedores
    $stmt = $conn->query("SELECT COUNT(*) FROM inv_suppliers WHERE is_active = 1");
    $stats['suppliers'] = $stmt->fetchColumn();

    // Total clientes
    $stmt = $conn->query("SELECT COUNT(*) FROM clients WHERE status = 'activo'");
    $stats['clients'] = $stmt->fetchColumn();

    // Ventas del mes
    $stmt = $conn->query("SELECT COUNT(*) FROM sales WHERE status = 'completada' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stats['sales_month'] = $stmt->fetchColumn();

    // Ingresos del mes
    $stmt = $conn->query("SELECT COALESCE(SUM(total), 0) FROM sales WHERE status = 'completada' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stats['revenue_month'] = $stmt->fetchColumn();

} catch (Exception $e) {
    $stats = ['products' => 0, 'low_stock' => 0, 'suppliers' => 0, 'clients' => 0, 'sales_month' => 0, 'revenue_month' => 0];
}

$user = $authSession->getUser();
?>

<div class="page-header-title" style="margin-bottom:var(--spacing-xl)">
    <h1 style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)">
        Bienvenido, <?php echo htmlspecialchars($user['username'] ?? ''); ?>
    </h1>
    <p style="color:var(--color-muted);margin-top:4px">Resumen del sistema</p>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $stats['products']; ?></div>
            <div class="stat-label">Productos Activos</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $stats['low_stock']; ?></div>
            <div class="stat-label">Stock Bajo</div>
            <?php if ($stats['low_stock'] > 0): ?>
                <div class="stat-change negative">Requiere atencion</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $stats['clients']; ?></div>
            <div class="stat-label">Clientes Activos</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="stat-info">
            <div class="stat-value">S/ <?php echo number_format($stats['revenue_month'], 2); ?></div>
            <div class="stat-label">Ingresos del Mes</div>
            <div class="stat-change positive"><?php echo $stats['sales_month']; ?> ventas</div>
        </div>
    </div>
</div>

<!-- Acciones rapidas -->
<div class="card" style="margin-bottom:var(--spacing-xl)">
    <div class="card-header">
        <h2 class="card-title">Acciones Rapidas</h2>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:var(--spacing-sm);flex-wrap:wrap">
            <a href="<?php echo BASE_URL; ?>/modules/sales/pos.php" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nueva Venta
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/inventory/products.php?action=new" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nuevo Producto
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/clients/list.php?action=new" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
                Nuevo Cliente
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/inventory/stock.php" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/>
                </svg>
                Ver Stock
            </a>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
