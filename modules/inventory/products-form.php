<?php
/**
 * Formulario de Producto - Proyecto Bee
 * Crear y editar productos
 */

$pageTitle = 'Formulario Producto';
$currentModule = 'inventory';
$currentPage = 'products';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/inventory.functions.php';

$session = Session::getInstance();
$product = null;
$isEdit = false;

if (!empty($_GET['id'])) {
    $product = invGetProductById($_GET['id']);
    if ($product) {
        $isEdit = true;
    }
}

// Procesar formulario (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $session->setFlash('error', 'Token de seguridad invalido.');
    } else {
        $data = [
            'id'          => $_POST['id'] ?? null,
            'code'        => sanitizeInput($_POST['code'] ?? ''),
            'name'        => sanitizeInput($_POST['name'] ?? ''),
            'description' => sanitizeInput($_POST['description'] ?? ''),
            'price'       => floatval($_POST['price'] ?? 0),
            'cost'        => floatval($_POST['cost'] ?? 0),
            'stock'       => intval($_POST['stock'] ?? 0),
            'min_stock'   => intval($_POST['min_stock'] ?? 5),
            'supplier_id' => $_POST['supplier_id'] ?: null
        ];

        if (empty($data['name'])) {
            $session->setFlash('error', 'El nombre del producto es obligatorio.');
        } else {
            invSaveProduct($data);
            $session->setFlash('success', 'Producto guardado correctamente.');
            header('Location: products.php');
            exit;
        }
    }
}

$pageTitle = $isEdit ? 'Editar Producto' : 'Formulario Producto';
$currentModule = 'inventory';
$currentPage = 'products';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$suppliers = invGetSuppliersList();

if (!$product) {
    $product = [
        'id' => '', 'code' => '', 'name' => '', 'description' => '',
        'price' => '0.00', 'cost' => '0.00', 'stock' => 0, 'min_stock' => 5, 'supplier_id' => ''
    ];
}
?>

<div class="toolbar">
    <div class="toolbar-left">
        <a href="products.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver
        </a>
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold);margin-left:var(--spacing-sm)">
            <?php echo $isEdit ? 'Editar Producto' : 'Nuevo Producto'; ?>
        </h1>
    </div>
</div>

<div class="card">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <?php endif; ?>

        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Codigo</label>
                    <input type="text" name="code" class="form-input"
                           value="<?php echo htmlspecialchars($product['code']); ?>"
                           placeholder="Codigo SKU o de barras">
                </div>
                <div class="form-group">
                    <label class="form-label-required">Nombre</label>
                    <input type="text" name="name" class="form-input" required
                           value="<?php echo htmlspecialchars($product['name']); ?>"
                           placeholder="Nombre del producto">
                </div>
            </div>

            <div class="form-group">
                <label>Descripcion</label>
                <textarea name="description" class="form-textarea" rows="2"
                          placeholder="Descripcion del producto"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio de Venta (S/)</label>
                    <input type="number" name="price" class="form-input" step="0.01" min="0"
                           value="<?php echo $product['price']; ?>">
                </div>
                <div class="form-group">
                    <label>Costo (S/)</label>
                    <input type="number" name="cost" class="form-input" step="0.01" min="0"
                           value="<?php echo $product['cost']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock Inicial</label>
                    <input type="number" name="stock" class="form-input" min="0"
                           value="<?php echo $product['stock']; ?>">
                </div>
                <div class="form-group">
                    <label>Stock Minimo</label>
                    <input type="number" name="min_stock" class="form-input" min="0"
                           value="<?php echo $product['min_stock']; ?>">
                    <span class="form-hint">Alerta cuando el stock baje de esta cantidad</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Proveedor</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">Sin proveedor</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['id']; ?>"
                                <?php echo ($product['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($supplier['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="form-actions" style="border:none;margin:0;padding:0">
                <a href="products.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo $isEdit ? 'Actualizar' : 'Crear'; ?> Producto
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
