<?php
/**
 * Stock - Proyecto Bee
 * Tabla de stock con filtros y alertas
 */

$pageTitle = 'Stock';
$currentModule = 'inventory';
$currentPage = 'stock';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/inventory.functions.php';

$session = Session::getInstance();

$page = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';

// Procesar ajuste rapido de stock (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'adjust_stock') {
    if ($session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $productId = $_POST['product_id'] ?? 0;
        $type = $_POST['type'] ?? 'entrada';
        $quantity = intval($_POST['quantity'] ?? 0);
        $notes = sanitizeInput($_POST['notes'] ?? '');

        if ($quantity > 0) {
            invRecordMovement($productId, $type, $quantity, 'Ajuste manual', $notes, $session->getUser()['id']);
            $session->setFlash('success', 'Stock actualizado correctamente.');
        } else {
            $session->setFlash('error', 'La cantidad debe ser mayor a 0.');
        }
    }
    header('Location: stock.php?' . http_build_query(['search' => $search, 'page' => $page]));
    exit;
}

$pageTitle = 'Stock';
$currentModule = 'inventory';
$currentPage = 'stock';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$stock = invGetStock($page, 15, $search);
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Stock</h1>
    </div>
    <div class="toolbar-right">
        <a href="products.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Gestionar Productos
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:var(--spacing-lg)">
    <div class="card-body">
        <form method="GET" class="search-input" style="width:100%;max-width:400px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Buscar por nombre o codigo..." data-table="stockTable">
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body no-padding">
        <div class="table-container">
            <table id="stockTable">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Proveedor</th>
                        <th>Precio</th>
                        <th>Costo</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th style="width:100px">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stock['items'])): ?>
                        <tr><td colspan="8"><div class="empty-state"><h3>No se encontraron productos</h3></div></td></tr>
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
                                <?php if ($product['stock'] <= 0): ?>
                                    <span class="badge badge-error">Agotado</span>
                                <?php elseif ($product['stock'] <= $product['min_stock']): ?>
                                    <span class="badge badge-warning">Bajo</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="openAdjustModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', <?php echo $product['stock']; ?>)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Ajustar
                                </button>
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

<!-- Modal Ajuste de Stock -->
<div class="modal-overlay" id="adjustModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Ajustar Stock</h3>
            <button class="modal-close" onclick="closeModal('adjustModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="adjust_stock">
            <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
            <input type="hidden" name="product_id" id="adj_product_id">
            <div class="modal-body">
                <p id="adj_product_name" style="font-weight:var(--font-weight-semibold);margin-bottom:var(--spacing-md)"></p>
                <p id="adj_current_stock" style="color:var(--color-muted);font-size:var(--font-size-sm);margin-bottom:var(--spacing-md)"></p>
                <div class="form-group">
                    <label>Tipo de Movimiento</label>
                    <select name="type" class="form-select" required>
                        <option value="entrada">Entrada (+)</option>
                        <option value="salida">Salida (-)</option>
                        <option value="ajuste">Ajuste</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label-required">Cantidad</label>
                    <input type="number" name="quantity" class="form-input" required min="1" value="1">
                </div>
                <div class="form-group">
                    <label>Notas</label>
                    <input type="text" name="notes" class="form-input" placeholder="Motivo del ajuste">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('adjustModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Ajuste</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdjustModal(id, name, stock) {
    document.getElementById('adj_product_id').value = id;
    document.getElementById('adj_product_name').textContent = 'Producto: ' + name;
    document.getElementById('adj_current_stock').textContent = 'Stock actual: ' + stock + ' unidades';
    openModal('adjustModal');
}
</script>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
