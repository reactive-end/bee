<?php
/**
 * Reportes de Ventas - Proyecto Bee
 * Estadisticas y graficos de ventas
 */

$pageTitle = 'Reportes';
$currentModule = 'sales';
$currentPage = 'reports';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/sales.functions.php';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$session = Session::getInstance();

// Fechas por defecto: este mes
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-t');

$report = salesGetReportData($dateFrom, $dateTo);
$totals = $report['totals'];
$daily = $report['daily'];
$topProducts = $report['top_products'];
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Reportes de Ventas</h1>
    </div>
    <div class="toolbar-right">
        <a href="export.php?format=excel&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Excel
        </a>
        <a href="export.php?format=pdf&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="btn btn-secondary" target="_blank">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 12H4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            PDF
        </a>
        <a href="history.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Historial
        </a>
    </div>
</div>

<!-- Filtros de fecha -->
<div class="card" style="margin-bottom:var(--spacing-lg)">
    <div class="card-body">
        <form method="GET" class="filter-row">
            <div class="form-group">
                <label>Desde</label>
                <input type="date" name="date_from" class="form-input" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="form-group">
                <label>Hasta</label>
                <input type="date" name="date_to" class="form-input" value="<?php echo $dateTo; ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary btn-filter">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Actualizar
                </button>
                <a href="reports.php" class="btn btn-secondary btn-filter">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Mes actual
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-value">S/ <?php echo number_format($totals['total_revenue'] ?? 0, 2); ?></div>
            <div class="stat-label">Ingresos Totales</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $totals['total_sales'] ?? 0; ?></div>
            <div class="stat-label">Ventas Totales</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-value">S/ <?php echo number_format($totals['avg_sale'] ?? 0, 2); ?></div>
            <div class="stat-label">Ticket Promedio</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-lg)">
    <!-- Ventas por dia -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Ventas por Dia</h2>
        </div>
        <div class="card-body no-padding">
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Fecha</th><th style="text-align:center">Ventas</th><th style="text-align:right">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daily)): ?>
                            <tr><td colspan="3" style="text-align:center;padding:2rem;color:var(--color-muted)">Sin datos en este periodo</td></tr>
                        <?php else: ?>
                            <?php foreach ($daily as $day): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($day['date'])); ?></td>
                                <td style="text-align:center"><?php echo $day['count']; ?></td>
                                <td style="text-align:right"><strong>S/ <?php echo number_format($day['total'], 2); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top productos -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Productos Mas Vendidos</h2>
        </div>
        <div class="card-body no-padding">
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>#</th><th>Producto</th><th style="text-align:center">Cantidad</th><th style="text-align:right">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--color-muted)">Sin datos en este periodo</td></tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $i => $prod): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                                <td style="text-align:center"><?php echo $prod['total_qty']; ?></td>
                                <td style="text-align:right">S/ <?php echo number_format($prod['total_rev'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
