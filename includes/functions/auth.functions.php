<?php
/**
 * Funciones de Autenticacion - Proyecto Bee
 * Login, logout, validacion de sesion y utilidades
 */

require_once __DIR__ . '/../classes/Database.php';

/**
 * Autentica un usuario con usuario/email y contraseña
 *
 * @param string $username Usuario o email
 * @param string $password Contraseña en texto plano
 * @return array Resultado con success y message
 */
function login($username, $password) {
    $result = ['success' => false, 'message' => ''];

    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        $sql = "SELECT id, username, email, password, role, is_active 
                FROM auth_users 
                WHERE (username = :username OR email = :email) 
                AND is_active = 1 
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email' => $username
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            $result['message'] = 'Usuario o contraseña incorrectos';
            return $result;
        }

        if (!password_verify($password, $user['password'])) {
            $result['message'] = 'Usuario o contraseña incorrectos';
            return $result;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        $updateSql = "UPDATE auth_users SET last_login = NOW() WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([':id' => $user['id']]);

        $result['success'] = true;
        $result['message'] = 'Autenticacion exitosa';

    } catch (Exception $e) {
        error_log('Error en login: ' . $e->getMessage());
        $result['message'] = 'Error del sistema. Intente mas tarde.';
    }

    return $result;
}

/**
 * Verifica si hay una sesion activa valida
 *
 * @return bool true si el usuario esta autenticado
 */
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $sessionLifetime = 3600;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionLifetime)) {
        logout();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Cierra la sesion del usuario
 */
function logout() {
    $_SESSION = [];

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    session_destroy();
}

/**
 * Sanitiza una entrada de usuario
 *
 * @param string $input Texto a sanitizar
 * @return string Texto sanitizado
 */
function sanitizeInput($input) {
    if (is_null($input)) {
        return '';
    }

    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

    return $input;
}

/**
 * Genera un hash de contraseña seguro
 *
 * @param string $password Contraseña en texto plano
 * @return string Hash de la contraseña
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verifica si el usuario tiene un rol especifico
 *
 * @param string|array $allowedRoles Rol o roles permitidos
 * @return bool true si tiene el rol requerido
 */
function hasRole($allowedRoles) {
    if (!isLoggedIn()) {
        return false;
    }

    if (!isset($_SESSION['role'])) {
        return false;
    }

    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    return in_array($_SESSION['role'], $allowedRoles);
}

/**
 * Regenera el ID de sesion para prevenir session fixation
 */
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
