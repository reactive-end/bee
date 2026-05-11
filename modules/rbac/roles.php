<?php
/**
 * Roles - Proyecto Bee
 * Gestion de roles del sistema
 */

$pageTitle = 'Roles';
$currentModule = 'rbac';
$currentPage = 'roles';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/rbac.functions.php';

$session = Session::getInstance();

// Procesar formulario (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $session->setFlash('error', 'Token de seguridad invalido.');
    } elseif (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save':
                $data = [
                    'id' => $_POST['id'] ?? null,
                    'name' => sanitizeInput($_POST['name'] ?? ''),
                    'description' => sanitizeInput($_POST['description'] ?? '')
                ];
                if (empty($data['name'])) {
                    $session->setFlash('error', 'El nombre del rol es obligatorio.');
                } else {
                    rbacSaveRole($data);
                    rbacAudit($session->getUser()['id'], 'save_role', 'rbac', 'Rol: ' . $data['name']);
                    $session->setFlash('success', 'Rol guardado correctamente.');
                }
                break;

            case 'delete':
                $id = $_POST['id'] ?? 0;
                $role = rbacGetRoleById($id);
                if ($role && $role['name'] !== 'admin') {
                    rbacDeleteRole($id);
                    rbacAudit($session->getUser()['id'], 'delete_role', 'rbac', 'Rol: ' . $role['name']);
                    $session->setFlash('success', 'Rol eliminado correctamente.');
                } else {
                    $session->setFlash('error', 'No se puede eliminar este rol.');
                }
                break;
        }
    }
    header('Location: roles.php');
    exit;
}

$pageTitle = 'Roles';
$currentModule = 'rbac';
$currentPage = 'roles';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$roles = rbacGetRoles();
$editRole = null;

if (isset($_GET['edit'])) {
    $editRole = rbacGetRoleById($_GET['edit']);
}
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Roles</h1>
    </div>
    <div class="toolbar-right">
        <button class="btn btn-primary" onclick="openModal('roleModal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Rol
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body no-padding">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Descripcion</th>
                        <th>Permisos</th>
                        <th>Creado</th>
                        <th style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                    </svg>
                                    <h3>No hay roles registrados</h3>
                                    <button class="btn btn-primary" onclick="openModal('roleModal')">Crear Primer Rol</button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roles as $role): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($role['name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($role['description'] ?? '—'); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $role['permission_count']; ?> permisos</span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($role['created_at'])); ?></td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="permissions.php?role=<?php echo $role['id']; ?>" class="btn btn-sm btn-secondary" title="Gestionar permisos">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </a>
                                    <a href="?edit=<?php echo $role['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <?php if ($role['name'] !== 'admin'): ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar el rol <?php echo htmlspecialchars($role['name']); ?>?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $role['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
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
</div>

<!-- Modal Crear/Editar Rol -->
<div class="modal-overlay" id="roleModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title"><?php echo $editRole ? 'Editar Rol' : 'Nuevo Rol'; ?></h3>
            <button class="modal-close" onclick="closeModal('roleModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
            <?php if ($editRole): ?>
                <input type="hidden" name="id" value="<?php echo $editRole['id']; ?>">
            <?php endif; ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label-required">Nombre del Rol</label>
                    <input type="text" name="name" class="form-input" required
                           value="<?php echo $editRole ? htmlspecialchars($editRole['name']) : ''; ?>"
                           placeholder="Ej: administrador">
                </div>
                <div class="form-group">
                    <label>Descripcion</label>
                    <textarea name="description" class="form-textarea" rows="3"
                              placeholder="Descripcion del rol"><?php echo $editRole ? htmlspecialchars($editRole['description'] ?? '') : ''; ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('roleModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Rol</button>
            </div>
        </form>
    </div>
</div>

<?php if ($editRole): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() { openModal('roleModal'); });
</script>
<?php endif; ?>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
