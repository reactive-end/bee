<?php
/**
 * Historial de Ventas - Proyecto Bee
 * Listado de ventas con filtros
 */

$pageTitle = 'Historial de Ventas';
$currentModule = 'sales';
$currentPage = 'history';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/sales.functions.php';

$session = Session::getInstance();

$page = max(1, intval($_GET['page'] ?? 1));
$filters = [
    'status'    => $_GET['status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to'] ?? ''
];

// Anular venta (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if ($session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $result = salesCancel($_POST['id'] ?? 0);
        if ($result) {
            $session->setFlash('success', 'Venta anulada correctamente.');
        } else {
            $session->setFlash('error', 'No se pudo anular la venta.');
        }
    }
    header('Location: history.php');
    exit;
}

$pageTitle = 'Historial de Ventas';
$currentModule = 'sales';
$currentPage = 'history';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$history = salesGetHistory($page, 20, array_filter($filters));
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Historial de Ventas</h1>
    </div>
    <div class="toolbar-right">
        <a href="reports.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Reportes
        </a>
        <a href="pos.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva Venta
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card" style="margin-bottom:var(--spacing-lg)">
    <div class="card-body">
        <form method="GET" class="filter-row">
            <div class="form-group">
                <label>Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todas</option>
                    <option value="completada" <?php echo ($filters['status'] === 'completada') ? 'selected' : ''; ?>>Completadas</option>
                    <option value="anulada" <?php echo ($filters['status'] === 'anulada') ? 'selected' : ''; ?>>Anuladas</option>
                </select>
            </div>
            <div class="form-group">
                <label>Desde</label>
                <input type="date" name="date_from" class="form-input"
                       value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>
            <div class="form-group">
                <label>Hasta</label>
                <input type="date" name="date_to" class="form-input"
                       value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary btn-filter">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Filtrar
                </button>
                <a href="history.php" class="btn btn-secondary btn-filter">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body no-padding">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px">ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history['items'])): ?>
                        <tr><td colspan="7"><div class="empty-state"><h3>No se encontraron ventas</h3></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($history['items'] as $sale): ?>
                        <tr>
                            <td><strong>#<?php echo $sale['id']; ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($sale['client_name'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($sale['username'] ?? '—'); ?></td>
                            <td><strong>S/ <?php echo number_format($sale['total'], 2); ?></strong></td>
                            <td>
                                <?php if ($sale['status'] === 'completada'): ?>
                                    <span class="badge badge-success">Completada</span>
                                <?php else: ?>
                                    <span class="badge badge-error">Anulada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="receipt.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm btn-secondary" title="Ver comprobante">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <?php if ($sale['status'] === 'completada'): ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Anular la venta #<?php echo $sale['id']; ?>?')">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="id" value="<?php echo $sale['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Anular">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($history['pages'] > 1): ?>
    <div class="card-footer">
        <div class="pagination">
            <div class="pagination-info"><?php echo $history['total']; ?> ventas</div>
            <div class="pagination-pages">
                <?php for ($i = 1; $i <= $history['pages']; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $i])); ?>"
                       class="<?php echo ($i == $page) ? 'current' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
