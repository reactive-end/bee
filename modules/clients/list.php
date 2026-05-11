<?php
/**
 * Clientes - Proyecto Bee
 * Listado de clientes con busqueda y filtros
 */

$pageTitle = 'Clientes';
$currentModule = 'clients';
$currentPage = 'list';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/clients.functions.php';

$session = Session::getInstance();

$page = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Cambiar estado (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    if ($session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        clientsToggleStatus($_POST['id'] ?? 0, $_POST['status'] ?? 'activo');
        $session->setFlash('success', 'Estado del cliente actualizado.');
    }
    header('Location: list.php');
    exit;
}

$pageTitle = 'Clientes';
$currentModule = 'clients';
$currentPage = 'list';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$result = clientsGetAll($page, 15, $search, $status);
$clients = $result['items'];
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Clientes</h1>
    </div>
    <div class="toolbar-right">
        <a href="form.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Cliente
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:var(--spacing-lg)">
    <div class="card-body">
        <form method="GET" style="display:flex;gap:var(--spacing-sm);flex-wrap:wrap;align-items:flex-end">
            <div class="search-input" style="width:280px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Buscar cliente..." data-table="clientsTable">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <select name="status" class="form-select" onchange="this.form.submit()" style="height:38px">
                    <option value="">Todos los estados</option>
                    <option value="activo" <?php echo $status === 'activo' ? 'selected' : ''; ?>>Activos</option>
                    <option value="inactivo" <?php echo $status === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body no-padding">
        <div class="table-container">
            <table id="clientsTable">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Estado</th>
                        <th>Compras</th>
                        <th style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients)): ?>
                        <tr><td colspan="7"><div class="empty-state"><h3>No se encontraron clientes</h3></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td style="font-size:var(--font-size-xs)">
                                <?php echo htmlspecialchars($client['document_type'] ?? 'DNI'); ?>:
                                <?php echo htmlspecialchars($client['document_number'] ?? '—'); ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($client['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($client['email'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($client['phone'] ?? '—'); ?></td>
                            <td>
                                <?php if ($client['status'] === 'activo'): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-error">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($client['total_purchases'] > 0): ?>
                                    <?php echo $client['total_purchases']; ?>
                                    <span style="color:var(--color-muted);font-size:var(--font-size-xs)">
                                        (S/ <?php echo number_format($client['total_spent'], 2); ?>)
                                    </span>
                                <?php else: ?>
                                    <span style="color:var(--color-muted)">Sin compras</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="detail.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-secondary" title="Ver detalle">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="form.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $client['status'] === 'activo' ? 'inactivo' : 'activo'; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $client['status'] === 'activo' ? 'btn-danger' : 'btn-success'; ?>"
                                                title="<?php echo $client['status'] === 'activo' ? 'Desactivar' : 'Activar'; ?>">
                                            <?php if ($client['status'] === 'activo'): ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                                            <?php endif; ?>
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
    <?php if ($result['pages'] > 1): ?>
    <div class="card-footer">
        <div class="pagination">
            <div class="pagination-info"><?php echo $result['total']; ?> clientes</div>
            <div class="pagination-pages">
                <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                    <a href="?<?php echo http_build_query(['search' => $search, 'status' => $status, 'page' => $i]); ?>"
                       class="<?php echo ($i == $page) ? 'current' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
