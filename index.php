<?php
/**
 * Punto de entrada principal - Proyecto Bee
 * Redirige al login si no hay sesion activa
 */

session_start();

require_once 'includes/functions/auth.functions.php';

if (isLoggedIn()) {
    header('Location: modules/dashboard/index.php');
    exit;
}

header('Location: modules/auth/login.php');
exit;
