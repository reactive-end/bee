<?php
/**
 * Detalle de Cliente - Proyecto Bee
 * Informacion del cliente e historial de compras
 */

$pageTitle = 'Detalle Cliente';
$currentModule = 'clients';
$currentPage = 'list';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/clients.functions.php';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$session = Session::getInstance();
$clientId = $_GET['id'] ?? 0;

$client = clientsGetById($clientId);
if (!$client) {
    $session->setFlash('error', 'Cliente no encontrado.');
    header('Location: list.php');
    exit;
}

$purchases = clientsGetPurchases($clientId, 20);
?>

<div class="toolbar">
    <div class="toolbar-left">
        <a href="list.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver
        </a>
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold);margin-left:var(--spacing-sm)">
            <?php echo htmlspecialchars($client['name']); ?>
        </h1>
    </div>
    <div class="toolbar-right">
        <a href="form.php?id=<?php echo $client['id']; ?>" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Editar
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-lg)">
    <!-- Info del cliente -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Informacion del Cliente</h2>
        </div>
        <div class="card-body">
            <div style="display:grid;gap:var(--spacing-sm)">
                <div>
                    <small style="color:var(--color-muted)">Documento</small>
                    <div><?php echo htmlspecialchars($client['document_type']); ?>: <?php echo htmlspecialchars($client['document_number'] ?? '—'); ?></div>
                </div>
                <div>
                    <small style="color:var(--color-muted)">Email</small>
                    <div><?php echo htmlspecialchars($client['email'] ?? '—'); ?></div>
                </div>
                <div>
                    <small style="color:var(--color-muted)">Telefono</small>
                    <div><?php echo htmlspecialchars($client['phone'] ?? '—'); ?></div>
                </div>
                <div>
                    <small style="color:var(--color-muted)">Direccion</small>
                    <div><?php echo htmlspecialchars($client['address'] ?? '—'); ?></div>
                </div>
                <div>
                    <small style="color:var(--color-muted)">Estado</small>
                    <div>
                        <?php if ($client['status'] === 'activo'): ?>
                            <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-error">Inactivo</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <small style="color:var(--color-muted)">Cliente desde</small>
                    <div><?php echo date('d/m/Y', strtotime($client['created_at'])); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de compras -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Historial de Compras</h2>
        </div>
        <div class="card-body no-padding">
            <?php if (empty($purchases)): ?>
                <div style="padding:var(--spacing-xl);text-align:center;color:var(--color-muted)">
                    <p>No hay compras registradas para este cliente.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td>#<?php echo $purchase['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($purchase['created_at'])); ?></td>
                                <td><strong>S/ <?php echo number_format($purchase['total'], 2); ?></strong></td>
                                <td>
                                    <?php if ($purchase['status'] === 'completada'): ?>
                                        <span class="badge badge-success">Completada</span>
                                    <?php else: ?>
                                        <span class="badge badge-error">Anulada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($client['notes'])): ?>
<div class="card" style="margin-top:var(--spacing-lg)">
    <div class="card-header"><h3 class="card-title">Notas</h3></div>
    <div class="card-body"><p><?php echo nl2br(htmlspecialchars($client['notes'])); ?></p></div>
</div>
<?php endif; ?>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
