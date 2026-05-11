<?php
/**
 * Punto de entrada principal - Proyecto Bee
 * Redirige segun estado de sesion
 */

// Cargar configuracion
require_once __DIR__ . '/includes/config/constants.php';
require_once __DIR__ . '/includes/classes/Database.php';
require_once __DIR__ . '/includes/classes/Session.php';
require_once __DIR__ . '/includes/functions/auth.functions.php';

$session = Session::getInstance();

if ($session->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
    exit;
}

header('Location: ' . BASE_URL . '/modules/auth/login.php');
exit;
