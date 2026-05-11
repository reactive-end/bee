<?php
/**
 * Usuarios - Proyecto Bee
 * Gestion de usuarios del sistema
 */

$pageTitle = 'Usuarios';
$currentModule = 'rbac';
$currentPage = 'users';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/rbac.functions.php';

$session = Session::getInstance();

// Procesar acciones (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $session->setFlash('error', 'Token de seguridad invalido.');
    } elseif (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save':
                $data = [
                    'id' => $_POST['id'] ?? null,
                    'username' => sanitizeInput($_POST['username'] ?? ''),
                    'email' => sanitizeInput($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? '',
                    'role' => sanitizeInput($_POST['role'] ?? 'vendedor')
                ];
                if (empty($data['username']) || empty($data['email'])) {
                    $session->setFlash('error', 'Usuario y email son obligatorios.');
                } elseif (empty($data['id']) && empty($data['password'])) {
                    $session->setFlash('error', 'La contraseña es obligatoria para nuevos usuarios.');
                } else {
                    rbacSaveUser($data);
                    rbacAudit($session->getUser()['id'], 'save_user', 'rbac', 'Usuario: ' . $data['username']);
                    $session->setFlash('success', 'Usuario guardado correctamente.');
                }
                break;

            case 'toggle':
                $id = $_POST['id'] ?? 0;
                $status = $_POST['status'] ?? 1;
                if ($id != $session->getUser()['id']) {
                    rbacToggleUserStatus($id, $status);
                    $session->setFlash('success', 'Estado del usuario actualizado.');
                } else {
                    $session->setFlash('error', 'No puedes desactivar tu propio usuario.');
                }
                break;
        }
    }
    header('Location: users.php');
    exit;
}

$pageTitle = 'Usuarios';
$currentModule = 'rbac';
$currentPage = 'users';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$users = rbacGetUsers();
$roles = rbacGetRoles();
$editUser = null;

if (isset($_GET['edit'])) {
    $editUser = rbacGetUserById($_GET['edit']);
}
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Usuarios del Sistema</h1>
    </div>
    <div class="toolbar-right">
        <button class="btn btn-primary" onclick="openModal('userModal')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            Nuevo Usuario
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body no-padding">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Ultimo Acceso</th>
                        <th style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6"><div class="empty-state"><h3>No hay usuarios</h3></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($user['role']); ?></span></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-error">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '—'; ?></td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <?php if ($user['id'] != $session->getUser()['id']): ?>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $user['is_active'] ? 0 : 1; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $user['is_active'] ? 'btn-danger' : 'btn-success'; ?>"
                                                title="<?php echo $user['is_active'] ? 'Desactivar' : 'Activar'; ?>">
                                            <?php if ($user['is_active']): ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                            <?php else: ?>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                                            <?php endif; ?>
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

<!-- Modal Crear/Editar Usuario -->
<div class="modal-overlay" id="userModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title"><?php echo $editUser ? 'Editar Usuario' : 'Nuevo Usuario'; ?></h3>
            <button class="modal-close" onclick="closeModal('userModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
            <?php if ($editUser): ?>
                <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
            <?php endif; ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label-required">Usuario</label>
                    <input type="text" name="username" class="form-input" required
                           value="<?php echo $editUser ? htmlspecialchars($editUser['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label-required">Email</label>
                    <input type="email" name="email" class="form-input" required
                           value="<?php echo $editUser ? htmlspecialchars($editUser['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label><?php echo $editUser ? 'Nueva Contraseña (dejar vacio para mantener)' : 'Contraseña'; ?></label>
                    <input type="password" name="password" class="form-input"
                           <?php echo !$editUser ? 'required' : ''; ?>
                           minlength="6" placeholder="Minimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label-required">Rol</label>
                    <select name="role" class="form-select" required>
                        <option value="">Seleccione un rol</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role['name']); ?>"
                                <?php echo ($editUser && $editUser['role'] === $role['name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('userModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<?php if ($editUser): ?>
<script>document.addEventListener('DOMContentLoaded', function() { openModal('userModal'); });</script>
<?php endif; ?>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
