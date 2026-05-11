<?php
/**
 * Permisos - Proyecto Bee
 * Asignacion de permisos a roles
 */

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/rbac.functions.php';

$session = Session::getInstance();
$roleId = $_GET['role'] ?? null;
$roles = rbacGetRoles();
$selectedRole = null;

if ($roleId) {
    $selectedRole = rbacGetRoleById($roleId);
}

// Procesar guardado de permisos (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_permissions') {
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $session->setFlash('error', 'Token de seguridad invalido.');
    } else {
        $roleId = $_POST['role_id'] ?? 0;
        $permIds = $_POST['permissions'] ?? [];
        rbacSaveRolePermissions($roleId, $permIds);
        rbacAudit($session->getUser()['id'], 'update_permissions', 'rbac', 'Permisos actualizados para rol ID: ' . $roleId);
        $session->setFlash('success', 'Permisos actualizados correctamente.');
    }
    header('Location: permissions.php?role=' . $roleId);
    exit;
}

$pageTitle = 'Permisos';
$currentModule = 'rbac';
$currentPage = 'permissions';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$permissions = rbacGetPermissions();
$rolePermissions = $roleId ? rbacGetRolePermissions($roleId) : [];

// Agrupar permisos por modulo
$permsByModule = [];
foreach ($permissions as $perm) {
    $permsByModule[$perm['module']][] = $perm;
}
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Permisos</h1>
    </div>
    <div class="toolbar-right">
        <a href="roles.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver a Roles
        </a>
    </div>
</div>

<!-- Selector de rol -->
<div class="card" style="margin-bottom:var(--spacing-lg)">
    <div class="card-body">
        <form method="GET" action="" style="display:flex;align-items:flex-end;gap:var(--spacing-md);flex-wrap:wrap">
            <div class="form-group" style="margin-bottom:0;min-width:250px">
                <label>Seleccionar Rol</label>
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Seleccione un rol --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>" <?php echo ($roleId == $role['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedRole): ?>
<form method="POST">
    <input type="hidden" name="action" value="save_permissions">
    <input type="hidden" name="role_id" value="<?php echo $selectedRole['id']; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">

    <?php foreach ($permsByModule as $module => $perms): ?>
    <div class="card" style="margin-bottom:var(--spacing-md)">
        <div class="card-header">
            <h3 class="card-title" style="text-transform:capitalize"><?php echo htmlspecialchars($module); ?></h3>
            <small style="color:var(--color-muted)"><?php echo count($perms); ?> permisos</small>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:var(--spacing-sm)">
                <?php foreach ($perms as $perm): ?>
                <label style="display:flex;align-items:center;gap:var(--spacing-sm);padding:var(--spacing-sm);border-radius:var(--border-radius);cursor:pointer;transition:background var(--transition-fast)"
                       onmouseover="this.style.background='rgba(255,244,117,0.1)'"
                       onmouseout="this.style.background=''">
                    <input type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>"
                           <?php echo in_array($perm['id'], $rolePermissions) ? 'checked' : ''; ?>
                           style="width:18px;height:18px;accent-color:#6fff5c">
                    <div>
                        <div style="font-size:var(--font-size-sm);font-weight:var(--font-weight-medium)">
                            <?php echo htmlspecialchars($perm['name']); ?>
                        </div>
                        <?php if (!empty($perm['description'])): ?>
                        <div style="font-size:var(--font-size-xs);color:var(--color-muted)">
                            <?php echo htmlspecialchars($perm['description']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Guardar Permisos</button>
    </div>
</form>
<?php elseif ($roleId): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <p>Rol no encontrado.</p>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <h3>Seleccione un rol para gestionar sus permisos</h3>
                <p>Use el selector de arriba para elegir un rol y asignarle permisos.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
