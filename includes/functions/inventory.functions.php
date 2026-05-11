<?php
/**
 * Funciones de Inventario - Proyecto Bee
 * Stock, productos, proveedores y movimientos
 */

require_once __DIR__ . '/../classes/Database.php';

// ─── Productos ─────────────────────────────────

/**
 * Obtiene productos con stock, paginados
 */
function invGetStock($page = 1, $limit = 15, $search = '')
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $where = "WHERE p.is_active = 1";
    $params = [];

    if (!empty($search)) {
        $where .= " AND (p.name LIKE :search1 OR p.code LIKE :search2)";
        $params[':search1'] = "%$search%";
        $params[':search2'] = "%$search%";
    }

    // Total
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM inv_products p $where");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Items
    $offset = ($page - 1) * $limit;
    $sql = "SELECT p.*, s.name AS supplier_name
            FROM inv_products p
            LEFT JOIN inv_suppliers s ON p.supplier_id = s.id
            $where
            ORDER BY p.name
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
 * Obtiene un producto por ID
 */
function invGetProductById($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare(
        "SELECT p.*, s.name AS supplier_name
         FROM inv_products p
         LEFT JOIN inv_suppliers s ON p.supplier_id = s.id
         WHERE p.id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Crea o actualiza un producto
 */
function invSaveProduct($data)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    if (!empty($data['id'])) {
        $sql = "UPDATE inv_products SET
                    code = :code, name = :name, description = :description,
                    price = :price, cost = :cost, stock = :stock,
                    min_stock = :min_stock, supplier_id = :supplier_id
                WHERE id = :id";
        $params = [
            ':id'          => $data['id'],
            ':code'        => $data['code'] ?: null,
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? '',
            ':price'       => $data['price'] ?? 0,
            ':cost'        => $data['cost'] ?? 0,
            ':stock'       => $data['stock'] ?? 0,
            ':min_stock'   => $data['min_stock'] ?? 5,
            ':supplier_id' => $data['supplier_id'] ?: null
        ];
    } else {
        $sql = "INSERT INTO inv_products (code, name, description, price, cost, stock, min_stock, supplier_id)
                VALUES (:code, :name, :description, :price, :cost, :stock, :min_stock, :supplier_id)";
        $params = [
            ':code'        => $data['code'] ?: null,
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? '',
            ':price'       => $data['price'] ?? 0,
            ':cost'        => $data['cost'] ?? 0,
            ':stock'       => $data['stock'] ?? 0,
            ':min_stock'   => $data['min_stock'] ?? 5,
            ':supplier_id' => $data['supplier_id'] ?: null
        ];
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $data['id'] ?? $conn->lastInsertId();
}

/**
 * Elimina un producto (soft delete)
 */
function invDeleteProduct($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("UPDATE inv_products SET is_active = 0 WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// ─── Proveedores ──────────────────────────────

/**
 * Obtiene todos los proveedores activos
 */
function invGetSuppliers($search = '')
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $sql = "SELECT * FROM inv_suppliers WHERE is_active = 1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (name LIKE :search OR email LIKE :email)";
        $params[':search'] = "%$search%";
        $params[':email'] = "%$search%";
    }

    $sql .= " ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Obtiene un proveedor por ID
 */
function invGetSupplierById($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("SELECT * FROM inv_suppliers WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Crea o actualiza un proveedor
 */
function invSaveSupplier($data)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    if (!empty($data['id'])) {
        $sql = "UPDATE inv_suppliers SET name = :name, contact_name = :contact_name,
                    email = :email, phone = :phone, address = :address
                WHERE id = :id";
        $params = [
            ':id'           => $data['id'],
            ':name'         => $data['name'],
            ':contact_name' => $data['contact_name'] ?? '',
            ':email'        => $data['email'] ?? '',
            ':phone'        => $data['phone'] ?? '',
            ':address'      => $data['address'] ?? ''
        ];
    } else {
        $sql = "INSERT INTO inv_suppliers (name, contact_name, email, phone, address)
                VALUES (:name, :contact_name, :email, :phone, :address)";
        $params = [
            ':name'         => $data['name'],
            ':contact_name' => $data['contact_name'] ?? '',
            ':email'        => $data['email'] ?? '',
            ':phone'        => $data['phone'] ?? '',
            ':address'      => $data['address'] ?? ''
        ];
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $data['id'] ?? $conn->lastInsertId();
}

/**
 * Elimina un proveedor (soft delete)
 */
function invDeleteSupplier($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("UPDATE inv_suppliers SET is_active = 0 WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Obtiene proveedores como lista simple (para selects)
 */
function invGetSuppliersList()
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->query(
        "SELECT id, name FROM inv_suppliers WHERE is_active = 1 ORDER BY name"
    );
    return $stmt->fetchAll();
}

/**
 * Registra un movimiento de stock
 */
function invRecordMovement($productId, $type, $quantity, $reference = '', $notes = '', $userId = null)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare(
        "INSERT INTO inv_stock_movements (product_id, type, quantity, reference, notes, user_id)
         VALUES (:product_id, :type, :quantity, :reference, :notes, :user_id)"
    );
    $stmt->execute([
        ':product_id' => $productId,
        ':type'       => $type,
        ':quantity'   => $quantity,
        ':reference'  => $reference,
        ':notes'      => $notes,
        ':user_id'    => $userId ?? ($_SESSION['user_id'] ?? 1)
    ]);

    // Actualizar stock del producto
    $operator = ($type === 'entrada') ? '+' : '-';
    $conn->exec("UPDATE inv_products SET stock = stock $operator $quantity WHERE id = $productId");
}
