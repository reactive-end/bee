<?php
/**
 * Layout inicial - Proyecto Bee
 * Incluir al inicio de cada pagina de modulo
 *
 * Uso:
 *   $pageTitle = 'Stock';
 *   $currentModule = 'inventory';
 *   $currentPage = 'stock';
 *   require_once '../../includes/config/constants.php';
 *   require_once ROOT_PATH . '/includes/classes/Session.php';
 *   require_once ROOT_PATH . '/includes/functions/auth.functions.php';
 *   require_once TEMPLATES_PATH . '/partials/layout-start.php';
 */

// Verificar autenticacion
$authSession = Session::getInstance();
if (!$authSession->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Bee</title>
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_URL; ?>/img/logo.jpg">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/variables.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/app.css">
</head>
<body>
    <?php require_once TEMPLATES_PATH . '/partials/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once TEMPLATES_PATH . '/partials/header.php'; ?>
        <div class="page-content">
            <?php echo $authSession->renderFlashes(); ?>
