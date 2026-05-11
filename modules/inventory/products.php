<?php
/**
 * Productos - Proyecto Bee
 * Listado y gestion de productos
 */

$pageTitle = 'Productos';
$currentModule = 'inventory';
$currentPage = 'products';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/inventory.functions.php';

$session = Session::getInstance();

$page = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';

// Eliminar producto (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if ($session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        invDeleteProduct($_POST['id'] ?? 0);
        $session->setFlash('success', 'Producto eliminado correctamente.');
    }
    header('Location: products.php');
    exit;
}

$pageTitle = 'Productos';
$currentModule = 'inventory';
$currentPage = 'products';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$stock = invGetStock($page, 15, $search);
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Productos</h1>
    </div>
    <div class="toolbar-right">
        <a href="stock.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/></svg>
            Ver Stock
        </a>
        <a href="products-form.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Producto
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:var(--spacing-lg)">
    <div class="card-body">
        <form method="GET" class="search-input" style="width:100%;max-width:400px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Buscar producto..." data-table="productsTable">
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body no-padding">
        <div class="table-container">
            <table id="productsTable">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Proveedor</th>
                        <th>Precio</th>
                        <th>Costo</th>
                        <th>Stock</th>
                        <th style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stock['items'])): ?>
                        <tr><td colspan="7"><div class="empty-state"><h3>No se encontraron productos</h3></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($stock['items'] as $product): ?>
                        <tr>
                            <td style="font-family:var(--font-mono);font-size:var(--font-size-xs)"><?php echo htmlspecialchars($product['code'] ?? '—'); ?></td>
                            <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($product['supplier_name'] ?? '—'); ?></td>
                            <td>S/ <?php echo number_format($product['price'], 2); ?></td>
                            <td>S/ <?php echo number_format($product['cost'], 2); ?></td>
                            <td><strong><?php echo $product['stock']; ?></strong></td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="products-form.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este producto?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($stock['pages'] > 1): ?>
    <div class="card-footer">
        <div class="pagination">
            <div class="pagination-info"><?php echo $stock['total']; ?> productos</div>
            <div class="pagination-pages">
                <?php for ($i = 1; $i <= $stock['pages']; $i++): ?>
                    <a href="?<?php echo http_build_query(['search' => $search, 'page' => $i]); ?>"
                       class="<?php echo ($i == $page) ? 'current' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
