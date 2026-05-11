<?php
/**
 * Exportacion de Reportes - Proyecto Bee
 * Excel (CSV) y PDF (impresion)
 *
 * Parametros:
 *   format    = excel | pdf
 *   date_from = YYYY-MM-DD
 *   date_to   = YYYY-MM-DD
 */

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/sales.functions.php';

$session = Session::getInstance();
if (!$session->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit;
}

$format  = $_GET['format'] ?? 'excel';
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo   = $_GET['date_to'] ?? date('Y-m-t');

$report = salesGetReportData($dateFrom, $dateTo);

if ($format === 'excel') {
    exportExcel($report, $dateFrom, $dateTo);
} else {
    exportPdf($report, $dateFrom, $dateTo);
}

/**
 * Exporta los datos como CSV (compatible Excel)
 */
function exportExcel($report, $dateFrom, $dateTo)
{
    $filename = 'reporte_ventas_' . $dateFrom . '_al_' . $dateTo . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // BOM para UTF-8 en Excel
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');

    // ─── Resumen ───
    fputcsv($out, ['REPORTE DE VENTAS']);
    fputcsv($out, ['Periodo', $dateFrom . ' al ' . $dateTo]);
    fputcsv($out, ['']);
    fputcsv($out, ['RESUMEN']);
    fputcsv($out, ['Total Ventas', $report['totals']['total_sales'] ?? 0]);
    fputcsv($out, ['Ingresos Totales', 'S/ ' . number_format($report['totals']['total_revenue'] ?? 0, 2)]);
    fputcsv($out, ['Ticket Promedio', 'S/ ' . number_format($report['totals']['avg_sale'] ?? 0, 2)]);
    fputcsv($out, ['']);

    // ─── Ventas por dia ───
    fputcsv($out, ['VENTAS POR DIA']);
    fputcsv($out, ['Fecha', 'Cantidad Ventas', 'Total (S/)']);
    foreach ($report['daily'] as $day) {
        fputcsv($out, [
            date('d/m/Y', strtotime($day['date'])),
            $day['count'],
            number_format($day['total'], 2)
        ]);
    }
    fputcsv($out, ['']);

    // ─── Top Productos ───
    fputcsv($out, ['PRODUCTOS MAS VENDIDOS']);
    fputcsv($out, ['#', 'Producto', 'Cantidad', 'Total (S/)']);
    $i = 1;
    foreach ($report['top_products'] as $prod) {
        fputcsv($out, [
            $i++,
            $prod['name'],
            $prod['total_qty'],
            number_format($prod['total_rev'], 2)
        ]);
    }

    fclose($out);
    exit;
}

/**
 * Genera vista imprimible del reporte
 */
function exportPdf($report, $dateFrom, $dateTo)
{
    ?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - Bee</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: #2d2d2d;
            padding: 30px 40px;
            font-size: 13px;
            line-height: 1.5;
        }
        .report-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2d2d2d;
        }
        .report-header h1 { font-size: 22px; margin-bottom: 4px; }
        .report-header p { color: #9e9e9e; font-size: 13px; }
        .section { margin-bottom: 25px; }
        .section h2 {
            font-size: 15px;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e0e0e0;
        }
        .summary-grid {
            display: flex;
            gap: 25px;
            margin-bottom: 10px;
        }
        .summary-item { flex: 1; }
        .summary-label { font-size: 11px; color: #9e9e9e; text-transform: uppercase; }
        .summary-value { font-size: 20px; font-weight: 700; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th {
            text-align: left;
            padding: 6px 10px;
            font-size: 11px;
            text-transform: uppercase;
            color: #9e9e9e;
            border-bottom: 2px solid #e0e0e0;
        }
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .report-footer {
            margin-top: 30px;
            text-align: center;
            color: #9e9e9e;
            font-size: 11px;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
        }
        @media print {
            body { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Bee - Reporte de Ventas</h1>
        <p>Periodo: <?php echo date('d/m/Y', strtotime($dateFrom)); ?> al <?php echo date('d/m/Y', strtotime($dateTo)); ?></p>
    </div>

    <div class="section">
        <h2>Resumen</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Ventas</div>
                <div class="summary-value"><?php echo $report['totals']['total_sales'] ?? 0; ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Ingresos Totales</div>
                <div class="summary-value">S/ <?php echo number_format($report['totals']['total_revenue'] ?? 0, 2); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Ticket Promedio</div>
                <div class="summary-value">S/ <?php echo number_format($report['totals']['avg_sale'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Ventas por Dia</h2>
        <table>
            <thead>
                <tr><th>Fecha</th><th class="text-center">Cantidad</th><th class="text-right">Total</th></tr>
            </thead>
            <tbody>
                <?php if (empty($report['daily'])): ?>
                    <tr><td colspan="3" class="text-center">Sin datos en este periodo</td></tr>
                <?php else: ?>
                    <?php foreach ($report['daily'] as $day): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($day['date'])); ?></td>
                        <td class="text-center"><?php echo $day['count']; ?></td>
                        <td class="text-right">S/ <?php echo number_format($day['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Productos Mas Vendidos</h2>
        <table>
            <thead>
                <tr><th>#</th><th>Producto</th><th class="text-center">Cantidad</th><th class="text-right">Total</th></tr>
            </thead>
            <tbody>
                <?php if (empty($report['top_products'])): ?>
                    <tr><td colspan="4" class="text-center">Sin datos en este periodo</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($report['top_products'] as $prod): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($prod['name']); ?></td>
                        <td class="text-center"><?php echo $prod['total_qty']; ?></td>
                        <td class="text-right">S/ <?php echo number_format($prod['total_rev'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="report-footer">
        Generado el <?php echo date('d/m/Y H:i:s'); ?> - Bee v<?php echo APP_VERSION; ?>
    </div>

    <script>window.print();</script>
</body>
</html>
    <?php
    exit;
}
