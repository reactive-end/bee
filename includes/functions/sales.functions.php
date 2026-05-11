<?php
/**
 * Funciones de Ventas - Proyecto Bee
 * POS, historial, reportes y comprobantes
 */

require_once __DIR__ . '/../classes/Database.php';

/**
 * Busca productos para el POS
 */
function salesSearchProducts($query)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare(
        "SELECT id, code, name, price, stock
         FROM inv_products
         WHERE is_active = 1
           AND (name LIKE :query OR code LIKE :code)
           AND stock > 0
         ORDER BY name
         LIMIT 20"
    );
    $stmt->execute([':query' => "%$query%", ':code' => "%$query%"]);
    return $stmt->fetchAll();
}

/**
 * Obtiene producto por ID (para POS)
 */
function salesGetProductById($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare(
        "SELECT id, code, name, price, stock FROM inv_products WHERE id = :id AND is_active = 1 LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Crea una venta
 */
function salesCreate($data)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $conn->beginTransaction();
    try {
        // Crear venta
        $stmt = $conn->prepare(
            "INSERT INTO sales (client_id, user_id, total, notes) VALUES (:client_id, :user_id, :total, :notes)"
        );
        $stmt->execute([
            ':client_id' => $data['client_id'] ?: null,
            ':user_id'   => $data['user_id'],
            ':total'     => $data['total'],
            ':notes'     => $data['notes'] ?? ''
        ]);
        $saleId = $conn->lastInsertId();

        // Insertar items
        $itemStmt = $conn->prepare(
            "INSERT INTO sales_items (sale_id, product_id, quantity, price, subtotal) VALUES (:sale_id, :product_id, :quantity, :price, :subtotal)"
        );
        $stockStmt = $conn->prepare("UPDATE inv_products SET stock = stock - :qty WHERE id = :id AND stock >= :qty2");

        foreach ($data['items'] as $item) {
            $itemStmt->execute([
                ':sale_id'    => $saleId,
                ':product_id' => $item['product_id'],
                ':quantity'   => $item['quantity'],
                ':price'      => $item['price'],
                ':subtotal'   => $item['subtotal']
            ]);

            // Descontar stock
            $stockStmt->execute([
                ':qty'  => $item['quantity'],
                ':id'   => $item['product_id'],
                ':qty2' => $item['quantity']
            ]);

            // Registrar movimiento
            $movStmt = $conn->prepare(
                "INSERT INTO inv_stock_movements (product_id, type, quantity, reference, user_id)
                 VALUES (:pid, 'salida', :qty, :ref, :uid)"
            );
            $movStmt->execute([
                ':pid' => $item['product_id'],
                ':qty' => $item['quantity'],
                ':ref' => 'Venta #' . $saleId,
                ':uid' => $data['user_id']
            ]);
        }

        $conn->commit();
        return $saleId;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log('Error creando venta: ' . $e->getMessage());
        return false;
    }
}

/**
 * Anula una venta
 */
function salesCancel($saleId)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $conn->beginTransaction();
    try {
        // Marcar como anulada
        $stmt = $conn->prepare("UPDATE sales SET status = 'anulada' WHERE id = :id AND status = 'completada'");
        $stmt->execute([':id' => $saleId]);

        if ($stmt->rowCount() === 0) {
            $conn->rollBack();
            return false;
        }

        // Devolver stock
        $items = $conn->prepare("SELECT product_id, quantity FROM sales_items WHERE sale_id = :id");
        $items->execute([':id' => $saleId]);
        $updateStock = $conn->prepare("UPDATE inv_products SET stock = stock + :qty WHERE id = :id");

        foreach ($items->fetchAll() as $item) {
            $updateStock->execute([':qty' => $item['quantity'], ':id' => $item['product_id']]);
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log('Error anulando venta: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene historial de ventas con paginacion
 */
function salesGetHistory($page = 1, $limit = 20, $filters = [])
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $where = [];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = "s.status = :status";
        $params[':status'] = $filters['status'];
    }
    if (!empty($filters['client_id'])) {
        $where[] = "s.client_id = :client_id";
        $params[':client_id'] = $filters['client_id'];
    }
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(s.created_at) >= :date_from";
        $params[':date_from'] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(s.created_at) <= :date_to";
        $params[':date_to'] = $filters['date_to'];
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM sales s $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Items
    $offset = ($page - 1) * $limit;
    $sql = "SELECT s.*, c.name AS client_name, u.username
            FROM sales s
            LEFT JOIN clients c ON s.client_id = c.id
            LEFT JOIN auth_users u ON s.user_id = u.id
            $whereClause
            ORDER BY s.created_at DESC
            LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'pages' => ceil($total / $limit),
        'page'  => $page
    ];
}

/**
 * Obtiene una venta con sus items
 */
function salesGetById($id)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $sale = $conn->prepare(
        "SELECT s.*, c.name AS client_name, c.document_type, c.document_number, u.username
         FROM sales s
         LEFT JOIN clients c ON s.client_id = c.id
         LEFT JOIN auth_users u ON s.user_id = u.id
         WHERE s.id = :id LIMIT 1"
    );
    $sale->execute([':id' => $id]);
    $saleData = $sale->fetch();

    if (!$saleData) return null;

    $items = $conn->prepare(
        "SELECT si.*, p.name AS product_name, p.code AS product_code
         FROM sales_items si
         LEFT JOIN inv_products p ON si.product_id = p.id
         WHERE si.sale_id = :id"
    );
    $items->execute([':id' => $id]);
    $saleData['items'] = $items->fetchAll();

    return $saleData;
}

/**
 * Obtiene datos para reportes
 */
function salesGetReportData($dateFrom, $dateTo)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Ventas por dia
    $dailyStmt = $conn->prepare(
        "SELECT DATE(created_at) AS date, COUNT(*) AS count, COALESCE(SUM(total), 0) AS total
         FROM sales
         WHERE status = 'completada'
           AND DATE(created_at) BETWEEN :from AND :to
         GROUP BY DATE(created_at)
         ORDER BY date"
    );
    $dailyStmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
    $daily = $dailyStmt->fetchAll();

    // Totales
    $totalsStmt = $conn->prepare(
        "SELECT COUNT(*) AS total_sales, COALESCE(SUM(total), 0) AS total_revenue,
                COALESCE(AVG(total), 0) AS avg_sale
         FROM sales
         WHERE status = 'completada'
           AND DATE(created_at) BETWEEN :from AND :to"
    );
    $totalsStmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
    $totals = $totalsStmt->fetch();

    // Top productos
    $topStmt = $conn->prepare(
        "SELECT p.name, SUM(si.quantity) AS total_qty, SUM(si.subtotal) AS total_rev
         FROM sales_items si
         JOIN sales s ON si.sale_id = s.id
         JOIN inv_products p ON si.product_id = p.id
         WHERE s.status = 'completada'
           AND DATE(s.created_at) BETWEEN :from AND :to
         GROUP BY si.product_id, p.name
         ORDER BY total_qty DESC
         LIMIT 10"
    );
    $topStmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
    $topProducts = $topStmt->fetchAll();

    return [
        'daily'       => $daily,
        'totals'      => $totals,
        'top_products' => $topProducts
    ];
}
