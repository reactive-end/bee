<?php
/**
 * Funciones RBAC - Proyecto Bee
 * Gestion de roles, permisos y auditoria
 */

require_once __DIR__ . '/../classes/Database.php';

/**
 * Obtiene todos los roles activos
 */
function rbacGetRoles()
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->query(
        "SELECT r.*, (SELECT COUNT(*) FROM rbac_role_permissions rp WHERE rp.role_id = r.id) AS permission_count
         FROM rbac_roles r
         WHERE r.is_active = 1
         ORDER BY r.name"
    );
    return $stmt->fetchAll();
}

/**
 * Obtiene un rol por ID
 */
function rbacGetRoleById($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("SELECT * FROM rbac_roles WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Crea o actualiza un rol
 */
function rbacSaveRole($data)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    if (!empty($data['id'])) {
        $sql = "UPDATE rbac_roles SET name = :name, description = :description WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? ''
        ]);
        return $data['id'];
    } else {
        $sql = "INSERT INTO rbac_roles (name, description) VALUES (:name, :description)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? ''
        ]);
        return $conn->lastInsertId();
    }
}

/**
 * Elimina un rol (soft delete)
 */
function rbacDeleteRole($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("UPDATE rbac_roles SET is_active = 0 WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Obtiene todos los permisos
 */
function rbacGetPermissions()
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->query(
        "SELECT * FROM rbac_permissions ORDER BY module, name"
    );
    return $stmt->fetchAll();
}

/**
 * Obtiene los permisos asignados a un rol
 */
function rbacGetRolePermissions($roleId)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare(
        "SELECT permission_id FROM rbac_role_permissions WHERE role_id = :role_id"
    );
    $stmt->execute([':role_id' => $roleId]);
    return array_column($stmt->fetchAll(), 'permission_id');
}

/**
 * Asigna permisos a un rol
 */
function rbacSaveRolePermissions($roleId, $permissionIds)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $conn->beginTransaction();
    try {
        // Eliminar permisos existentes
        $stmt = $conn->prepare("DELETE FROM rbac_role_permissions WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);

        // Insertar nuevos permisos
        if (!empty($permissionIds)) {
            $stmt = $conn->prepare("INSERT INTO rbac_role_permissions (role_id, permission_id) VALUES (:role_id, :perm_id)");
            foreach ($permissionIds as $permId) {
                $stmt->execute([':role_id' => $roleId, ':perm_id' => $permId]);
            }
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log('Error guardando permisos de rol: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene usuarios del sistema
 */
function rbacGetUsers()
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->query(
        "SELECT id, username, email, role, is_active, last_login, created_at
         FROM auth_users
         ORDER BY username"
    );
    return $stmt->fetchAll();
}

/**
 * Obtiene un usuario por ID
 */
function rbacGetUserById($id)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare(
        "SELECT id, username, email, role, is_active FROM auth_users WHERE id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Crea o actualiza un usuario del sistema
 */
function rbacSaveUser($data)
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    if (!empty($data['id'])) {
        $sql = "UPDATE auth_users SET username = :username, email = :email, role = :role";
        $params = [
            ':id' => $data['id'],
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':role' => $data['role']
        ];

        if (!empty($data['password'])) {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $sql .= " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $data['id'];
    } else {
        $sql = "INSERT INTO auth_users (username, email, password, role) VALUES (:username, :email, :password, :role)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':role' => $data['role']
        ]);
        return $conn->lastInsertId();
    }
}

/**
 * Activa/desactiva un usuario
 */
function rbacToggleUserStatus($id, $status)
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare("UPDATE auth_users SET is_active = :status WHERE id = :id");
    return $stmt->execute([':id' => $id, ':status' => $status]);
}

/**
 * Obtiene el log de auditoria con paginacion
 */
function rbacGetAuditLog($page = 1, $limit = 25, $filters = [])
{
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $where = [];
    $params = [];

    if (!empty($filters['module'])) {
        $where[] = "a.module = :module";
        $params[':module'] = $filters['module'];
    }
    if (!empty($filters['user_id'])) {
        $where[] = "a.user_id = :user_id";
        $params[':user_id'] = $filters['user_id'];
    }
    if (!empty($filters['action'])) {
        $where[] = "a.action = :action";
        $params[':action'] = $filters['action'];
    }
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(a.created_at) >= :date_from";
        $params[':date_from'] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(a.created_at) <= :date_to";
        $params[':date_to'] = $filters['date_to'];
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Contar total
    $countSql = "SELECT COUNT(*) FROM rbac_audit_log a $whereClause";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Obtener registros
    $offset = ($page - 1) * $limit;
    $sql = "SELECT a.*, au.username
            FROM rbac_audit_log a
            LEFT JOIN auth_users au ON a.user_id = au.id
            $whereClause
            ORDER BY a.created_at DESC
            LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    return [
        'items' => $items,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'page' => $page
    ];
}

/**
 * Registra una accion en auditoria
 */
function rbacAudit($userId, $action, $module, $description = '')
{
    $db = Database::getInstance();
    $stmt = $db->getConnection()->prepare(
        "INSERT INTO rbac_audit_log (user_id, action, module, description, ip_address)
         VALUES (:user_id, :action, :module, :description, :ip)"
    );
    return $stmt->execute([
        ':user_id' => $userId,
        ':action' => $action,
        ':module' => $module,
        ':description' => $description,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);
}
