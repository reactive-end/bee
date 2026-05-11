<?php
/**
 * Logout - Proyecto Bee
 * Cierra la sesion y redirige al login
 */

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';

$session = Session::getInstance();
$session->destroy();

header('Location: ' . BASE_URL . '/modules/auth/login.php');
exit;
