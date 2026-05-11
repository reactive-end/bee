<?php
/**
 * Auditoria - Proyecto Bee
 * Registro de acciones del sistema
 */

$pageTitle = 'Auditoria';
$currentModule = 'rbac';
$currentPage = 'audit';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/rbac.functions.php';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$session = Session::getInstance();

$page = max(1, intval($_GET['page'] ?? 1));
$filters = [
    'module' => $_GET['module'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$log = rbacGetAuditLog($page, 25, array_filter($filters));

// Modulos disponibles
$modules = ['dashboard', 'inventory', 'rbac', 'clients', 'sales'];
?>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Auditoria</h1>
    </div>
</div>

<!-- Filtros -->
<div class="card" style="margin-bottom:var(--spacing-lg)">
    <div class="card-body">
        <form method="GET" class="filter-row">
            <div class="form-group">
                <label>Modulo</label>
                <select name="module" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($modules as $mod): ?>
                        <option value="<?php echo $mod; ?>" <?php echo ($filters['module'] === $mod) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($mod); ?>
                        </option>
                    <?php endforeach; ?>
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
                <a href="audit-log.php" class="btn btn-secondary btn-filter">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de auditoria -->
<div class="card">
    <div class="card-body no-padding">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px">ID</th>
                        <th>Usuario</th>
                        <th>Accion</th>
                        <th>Modulo</th>
                        <th>Descripcion</th>
                        <th>IP</th>
                        <th style="width:160px">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($log['items'])): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    <h3>No hay registros de auditoria</h3>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($log['items'] as $entry): ?>
                        <tr>
                            <td><?php echo $entry['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($entry['username'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo htmlspecialchars($entry['action']); ?></td>
                            <td><span class="badge badge-neutral"><?php echo htmlspecialchars($entry['module']); ?></span></td>
                            <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                <?php echo htmlspecialchars($entry['description'] ?? '—'); ?>
                            </td>
                            <td style="font-family:var(--font-mono);font-size:var(--font-size-xs)"><?php echo htmlspecialchars($entry['ip_address'] ?? '—'); ?></td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($entry['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($log['pages'] > 1): ?>
    <div class="card-footer">
        <div class="pagination">
            <div class="pagination-info">
                Mostrando pagina <?php echo $page; ?> de <?php echo $log['pages']; ?> (<?php echo $log['total']; ?> registros)
            </div>
            <div class="pagination-pages">
                <?php
                $query = http_build_query(array_merge($filters, ['page' => '']));
                for ($i = 1; $i <= $log['pages']; $i++):
                ?>
                    <a href="?<?php echo $query . $i; ?>" class="<?php echo ($i == $page) ? 'current' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
