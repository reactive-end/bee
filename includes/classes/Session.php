<?php
/**
 * Clase Session - Proyecto Bee
 * Gestion centralizada de sesiones, CSRF y mensajes flash
 * PHP 7.4+
 */

class Session
{
    private static $instance = null;
    private $flashKey = 'bee_flash_messages';

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        $this->cleanFlashMessages();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ─── Autenticacion ─────────────────────────────

    /**
     * Inicia sesion para un usuario
     */
    public function login($user)
    {
        $this->regenerate();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }

    /**
     * Verifica si el usuario esta autenticado
     */
    public function isLoggedIn()
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        if (isset($_SESSION['last_activity'])
            && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            $this->destroy();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Obtiene datos del usuario en sesion
     */
    public function getUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return [
            'id'       => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email'    => $_SESSION['email'] ?? null,
            'role'     => $_SESSION['role'] ?? null,
        ];
    }

    /**
     * Verifica si el usuario tiene un rol especifico
     */
    public function hasRole($roles)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        if (is_string($roles)) {
            $roles = [$roles];
        }
        return in_array($_SESSION['role'] ?? '', $roles, true);
    }

    /**
     * Cierra la sesion
     */
    public function destroy()
    {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        session_destroy();
    }

    /**
     * Regenera el ID de sesion (previene session fixation)
     */
    public function regenerate()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    // ─── CSRF ──────────────────────────────────────

    /**
     * Genera y almacena un token CSRF
     */
    public function generateCsrfToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_NAME] = $token;
        return $token;
    }

    /**
     * Obtiene el token CSRF actual (lo genera si no existe)
     */
    public function getCsrfToken()
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            return $this->generateCsrfToken();
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Valida un token CSRF recibido
     */
    public function validateCsrfToken($token)
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Renderiza un campo hidden con el token CSRF
     */
    public function csrfField()
    {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME
            . '" value="' . $this->getCsrfToken() . '">';
    }

    // ─── Flash Messages ────────────────────────────

    /**
     * Almacena un mensaje flash
     */
    public function setFlash($type, $message)
    {
        $_SESSION[$this->flashKey][] = [
            'type'    => $type,   // success, error, warning, info
            'message' => $message,
        ];
    }

    /**
     * Obtiene y limpia los mensajes flash
     */
    public function getFlashes()
    {
        $messages = $_SESSION[$this->flashKey] ?? [];
        unset($_SESSION[$this->flashKey]);
        return $messages;
    }

    /**
     * Verifica si hay mensajes flash pendientes
     */
    public function hasFlashes()
    {
        return !empty($_SESSION[$this->flashKey]);
    }

    /**
     * Renderiza los mensajes flash como HTML
     */
    public function renderFlashes()
    {
        $messages = $this->getFlashes();
        if (empty($messages)) {
            return '';
        }

        $html = '';
        foreach ($messages as $msg) {
            $icon = $this->getFlashIcon($msg['type']);
            $html .= sprintf(
                '<div class="alert alert-%s">%s<span>%s</span></div>',
                htmlspecialchars($msg['type']),
                $icon,
                htmlspecialchars($msg['message'])
            );
        }
        return $html;
    }

    private function getFlashIcon($type)
    {
        $icons = [
            'success' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
            'error'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
            'warning' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            'info'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
        ];
        return $icons[$type] ?? $icons['info'];
    }

    // ─── Interno ───────────────────────────────────

    private function cleanFlashMessages()
    {
        // Los mensajes flash ya se limpian al ser leidos con getFlashes()
    }

    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception("No se puede deserializar una instancia singleton");
    }
}
