<?php
/**
 * Formulario de Proveedor - Proyecto Bee
 * Crear y editar proveedores
 */

$pageTitle = 'Formulario Proveedor';
$currentModule = 'inventory';
$currentPage = 'suppliers';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/inventory.functions.php';

$session = Session::getInstance();
$supplier = null;
$isEdit = false;

if (!empty($_GET['id'])) {
    $supplier = invGetSupplierById($_GET['id']);
    if ($supplier) {
        $isEdit = true;
    }
}

// Procesar formulario (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $session->setFlash('error', 'Token de seguridad invalido.');
    } else {
        $data = [
            'id'           => $_POST['id'] ?? null,
            'name'         => sanitizeInput($_POST['name'] ?? ''),
            'contact_name' => sanitizeInput($_POST['contact_name'] ?? ''),
            'email'        => sanitizeInput($_POST['email'] ?? ''),
            'phone'        => sanitizeInput($_POST['phone'] ?? ''),
            'address'      => sanitizeInput($_POST['address'] ?? '')
        ];

        if (empty($data['name'])) {
            $session->setFlash('error', 'El nombre del proveedor es obligatorio.');
        } else {
            invSaveSupplier($data);
            $session->setFlash('success', 'Proveedor guardado correctamente.');
            header('Location: suppliers.php');
            exit;
        }
    }
}

$pageTitle = $isEdit ? 'Editar Proveedor' : 'Formulario Proveedor';
$currentModule = 'inventory';
$currentPage = 'suppliers';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

if (!$supplier) {
    $supplier = ['id' => '', 'name' => '', 'contact_name' => '', 'email' => '', 'phone' => '', 'address' => ''];
}
?>

<div class="toolbar">
    <div class="toolbar-left">
        <a href="suppliers.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver
        </a>
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold);margin-left:var(--spacing-sm)">
            <?php echo $isEdit ? 'Editar Proveedor' : 'Nuevo Proveedor'; ?>
        </h1>
    </div>
</div>

<div class="card">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
        <?php endif; ?>

        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-required">Nombre / Razon Social</label>
                    <input type="text" name="name" class="form-input" required
                           value="<?php echo htmlspecialchars($supplier['name']); ?>">
                </div>
                <div class="form-group">
                    <label>Nombre de Contacto</label>
                    <input type="text" name="contact_name" class="form-input"
                           value="<?php echo htmlspecialchars($supplier['contact_name']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-input"
                           value="<?php echo htmlspecialchars($supplier['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Telefono</label>
                    <input type="text" name="phone" class="form-input"
                           value="<?php echo htmlspecialchars($supplier['phone']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Direccion</label>
                <textarea name="address" class="form-textarea" rows="2"><?php echo htmlspecialchars($supplier['address']); ?></textarea>
            </div>
        </div>

        <div class="card-footer">
            <div class="form-actions" style="border:none;margin:0;padding:0">
                <a href="suppliers.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo $isEdit ? 'Actualizar' : 'Crear'; ?> Proveedor
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
