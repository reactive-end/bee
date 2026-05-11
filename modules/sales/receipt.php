<?php
/**
 * Comprobante de Venta - Proyecto Bee
 * Muestra el detalle de una venta
 */

$pageTitle = 'Comprobante';
$currentModule = 'sales';
$currentPage = 'history';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/sales.functions.php';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

$session = Session::getInstance();
$saleId = $_GET['id'] ?? 0;
$sale = salesGetById($saleId);

if (!$sale) {
    $session->setFlash('error', 'Venta no encontrada.');
    header('Location: history.php');
    exit;
}
?>

<style>
.receipt-container {
    max-width: 700px;
    margin: 0 auto;
}
.receipt-header {
    text-align: center;
    padding-bottom: var(--spacing-lg);
    border-bottom: 2px dashed var(--color-border);
    margin-bottom: var(--spacing-lg);
}
.receipt-header h2 { margin-bottom: 4px; }
.receipt-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}
.receipt-items { margin-bottom: var(--spacing-lg); }
.receipt-total {
    text-align: right;
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    padding-top: var(--spacing-md);
    border-top: 2px solid var(--color-text);
}
.receipt-footer {
    text-align: center;
    color: var(--color-muted);
    font-size: var(--font-size-sm);
    margin-top: var(--spacing-xl);
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--color-border);
}
.receipt-actions {
    display: flex;
    justify-content: center;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-lg);
}
@media print {
    .sidebar, .page-header, .receipt-actions, .toolbar { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .page-content { padding: 0 !important; }
    body { background: white !important; }
}
</style>

<div class="toolbar">
    <div class="toolbar-left">
        <a href="history.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver
        </a>
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold);margin-left:var(--spacing-sm)">
            Venta #<?php echo $sale['id']; ?>
        </h1>
    </div>
</div>

<div class="card receipt-container">
    <div class="card-body">
        <div class="receipt-header">
            <h2 style="font-size:var(--font-size-2xl)">Bee</h2>
            <p style="color:var(--color-muted)">Sistema de Gestion</p>
            <h3 style="margin-top:var(--spacing-md)">Comprobante de Venta</h3>
            <p style="color:var(--color-muted)">#<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></p>
        </div>

        <div class="receipt-info">
            <div>
                <small style="color:var(--color-muted)">Fecha</small>
                <div><strong><?php echo date('d/m/Y H:i:s', strtotime($sale['created_at'])); ?></strong></div>
            </div>
            <div>
                <small style="color:var(--color-muted)">Estado</small>
                <div>
                    <?php if ($sale['status'] === 'completada'): ?>
                        <span class="badge badge-success">Completada</span>
                    <?php else: ?>
                        <span class="badge badge-error">Anulada</span>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <small style="color:var(--color-muted)">Vendedor</small>
                <div><?php echo htmlspecialchars($sale['username'] ?? '—'); ?></div>
            </div>
            <div>
                <small style="color:var(--color-muted)">Cliente</small>
                <div>
                    <?php if ($sale['client_name']): ?>
                        <strong><?php echo htmlspecialchars($sale['client_name']); ?></strong>
                        <?php if ($sale['document_number']): ?>
                            <br><small><?php echo htmlspecialchars($sale['document_type']); ?>: <?php echo htmlspecialchars($sale['document_number']); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:var(--color-muted)">Cliente generico</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="receipt-items">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th style="text-align:center">Cant.</th>
                        <th style="text-align:right">P. Unit.</th>
                        <th style="text-align:right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sale['items'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name'] ?? 'Producto #' . $item['product_id']); ?></td>
                        <td style="text-align:center"><?php echo $item['quantity']; ?></td>
                        <td style="text-align:right">S/ <?php echo number_format($item['price'], 2); ?></td>
                        <td style="text-align:right"><strong>S/ <?php echo number_format($item['subtotal'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="receipt-total">
            TOTAL: S/ <?php echo number_format($sale['total'], 2); ?>
        </div>

        <?php if (!empty($sale['notes'])): ?>
        <div style="margin-top:var(--spacing-md);padding:var(--spacing-sm);background:rgba(255,244,117,0.15);border-radius:var(--border-radius)">
            <small style="color:var(--color-muted)">Notas:</small>
            <p style="font-size:var(--font-size-sm)"><?php echo nl2br(htmlspecialchars($sale['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="receipt-footer">
            <p>Gracias por su compra</p>
            <p><?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></p>
        </div>

        <div class="receipt-actions">
            <button class="btn btn-secondary" onclick="window.print()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 12H4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir
            </button>
            <a href="history.php" class="btn btn-secondary">Volver al Historial</a>
        </div>
    </div>
</div>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
