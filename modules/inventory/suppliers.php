<?php
/**
 * Proveedores - Proyecto Bee
 * Listado y gestion de proveedores
 */

$pageTitle = 'Proveedores';
$currentModule = 'inventory';
$currentPage = 'suppliers';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/inventory.functions.php';

$session = Session::getInstance();
$search = $_GET['search'] ?? '';

// Eliminar proveedor (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if ($session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        invDeleteSupplier($_POST['id'] ?? 0);
        $session->setFlash('success', 'Proveedor eliminado correctamente.');
    }
    header('Location: suppliers.php');
    exit;
}

$pageTitle = 'Proveedores';
$currentModule = 'inventory';
$currentPage = 'suppliers';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$suppliers = invGetSuppliers($search);
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Proveedores</h1>
    </div>
    <div class="toolbar-right">
        <a href="suppliers-form.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Proveedor
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:var(--spacing-lg)">
    <div class="card-body">
        <form method="GET" class="search-input" style="width:100%;max-width:400px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Buscar proveedor..." data-table="suppliersTable">
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body no-padding">
        <div class="table-container">
            <table id="suppliersTable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suppliers)): ?>
                        <tr><td colspan="5"><div class="empty-state"><h3>No se encontraron proveedores</h3></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($supplier['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($supplier['contact_name'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($supplier['email'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($supplier['phone'] ?? '—'); ?></td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="suppliers-form.php?id=<?php echo $supplier['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este proveedor?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
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
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
