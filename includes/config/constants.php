<?php
/**
 * Constantes Globales - Proyecto Bee
 * Definiciones de rutas, URLs y configuracion general
 */

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__, 2));       // Raiz del proyecto
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// URLs (ajustar segun entorno)
define('BASE_URL', '/bee');
define('ASSETS_URL', BASE_URL . '/assets');
define('MODULES_URL', BASE_URL . '/modules');

// Aplicacion
define('APP_NAME', 'Bee');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Sistema de Gestion');

// Sesion
define('SESSION_LIFETIME', 3600);              // 1 hora
define('SESSION_NAME', 'BEE_SESSION');

// Seguridad
define('CSRF_TOKEN_NAME', 'bee_csrf_token');
define('PASSWORD_BCRYPT_COST', 12);

// Paginacion
define('ITEMS_PER_PAGE', 15);
define('MAX_ITEMS_PER_PAGE', 100);

// Zona horaria
date_default_timezone_set('America/Lima');
