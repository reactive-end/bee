<?php
/**
 * Funciones de Clientes - Proyecto Bee
 * CRUD y busqueda de clientes
 */

require_once __DIR__ . '/../classes/Database.php';

/**
 * Obtiene clientes con paginacion
 */
function clientsGetAll($page = 1, $limit = 15, $search = '', $status = '')
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(c.name LIKE :search1 OR c.document_number LIKE :search2 OR c.email LIKE :search3)";
        $params[':search1'] = "%$search%";
        $params[':search2'] = "%$search%";
        $params[':search3'] = "%$search%";
    }
    if (!empty($status)) {
        $where[] = "c.status = :status";
        $params[':status'] = $status;
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM clients c $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Items
    $offset = ($page - 1) * $limit;
    $sql = "SELECT c.*,
                   (SELECT COUNT(*) FROM sales s WHERE s.client_id = c.id) AS total_purchases,
                   (SELECT COALESCE(SUM(s.total), 0) FROM sales s WHERE s.client_id = c.id AND s.status = 'completada') AS total_spent
            FROM clients c
            $whereClause
            ORDER BY c.name
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
 * Obtiene un cliente por ID
 */
function clientsGetById($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Busca clientes (para autocompletado en POS)
 */
function clientsSearch($query, $limit = 10)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare(
        "SELECT id, name, document_type, document_number
         FROM clients
         WHERE status = 'activo'
           AND (name LIKE :query OR document_number LIKE :doc)
         ORDER BY name
         LIMIT :limit"
    );
    $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
    $stmt->bindValue(':doc', "%$query%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Crea o actualiza un cliente
 */
function clientsSave($data)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    if (!empty($data['id'])) {
        $sql = "UPDATE clients SET
                    document_type = :doc_type, document_number = :doc_num,
                    name = :name, email = :email, phone = :phone,
                    address = :address, status = :status, notes = :notes
                WHERE id = :id";
        $params = [
            ':id'        => $data['id'],
            ':doc_type'  => $data['document_type'] ?? 'DNI',
            ':doc_num'   => $data['document_number'] ?? null,
            ':name'      => $data['name'],
            ':email'     => $data['email'] ?? '',
            ':phone'     => $data['phone'] ?? '',
            ':address'   => $data['address'] ?? '',
            ':status'    => $data['status'] ?? 'activo',
            ':notes'     => $data['notes'] ?? ''
        ];
    } else {
        $sql = "INSERT INTO clients (document_type, document_number, name, email, phone, address, status, notes)
                VALUES (:doc_type, :doc_num, :name, :email, :phone, :address, :status, :notes)";
        $params = [
            ':doc_type'  => $data['document_type'] ?? 'DNI',
            ':doc_num'   => $data['document_number'] ?? null,
            ':name'      => $data['name'],
            ':email'     => $data['email'] ?? '',
            ':phone'     => $data['phone'] ?? '',
            ':address'   => $data['address'] ?? '',
            ':status'    => $data['status'] ?? 'activo',
            ':notes'     => $data['notes'] ?? ''
        ];
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $data['id'] ?? $conn->lastInsertId();
}

/**
 * Cambia el estado de un cliente
 */
function clientsToggleStatus($id, $status)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("UPDATE clients SET status = :status WHERE id = :id");
    return $stmt->execute([':id' => $id, ':status' => $status]);
}

/**
 * Obtiene historial de compras de un cliente
 */
function clientsGetPurchases($clientId, $limit = 10)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare(
        "SELECT s.*, u.username
         FROM sales s
         LEFT JOIN auth_users u ON s.user_id = u.id
         WHERE s.client_id = :client_id
         ORDER BY s.created_at DESC
         LIMIT :limit"
    );
    $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
