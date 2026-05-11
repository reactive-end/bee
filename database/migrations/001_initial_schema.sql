-- ============================================
-- Proyecto Bee - Migracion Inicial v1.0.0
-- Base de datos: bee_system
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── Auth: Usuarios del sistema ─────────────

CREATE TABLE IF NOT EXISTS `auth_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'vendedor',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_auth_users_role` (`role`),
    INDEX `idx_auth_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── RBAC: Roles ────────────────────────────

CREATE TABLE IF NOT EXISTS `rbac_roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── RBAC: Permisos ─────────────────────────

CREATE TABLE IF NOT EXISTS `rbac_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `module` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rbac_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── RBAC: Rol-Permiso (pivot) ──────────────

CREATE TABLE IF NOT EXISTS `rbac_role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `rbac_roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `rbac_permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── RBAC: Auditoria ────────────────────────

CREATE TABLE IF NOT EXISTS `rbac_audit_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_module` (`module`),
    INDEX `idx_audit_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `auth_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Inventory: Proveedores ──────────────────

CREATE TABLE IF NOT EXISTS `inv_suppliers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `contact_name` VARCHAR(100) NULL,
    `email` VARCHAR(100) NULL,
    `phone` VARCHAR(20) NULL,
    `address` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Inventory: Productos ────────────────────

CREATE TABLE IF NOT EXISTS `inv_products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock` INT NOT NULL DEFAULT 0,
    `min_stock` INT NOT NULL DEFAULT 5,
    `supplier_id` INT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_products_code` (`code`),
    INDEX `idx_products_supplier` (`supplier_id`),
    INDEX `idx_products_active` (`is_active`),
    FOREIGN KEY (`supplier_id`) REFERENCES `inv_suppliers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Inventory: Movimientos de stock ─────────

CREATE TABLE IF NOT EXISTS `inv_stock_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `type` ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    `quantity` INT NOT NULL,
    `reference` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_movements_product` (`product_id`),
    INDEX `idx_movements_type` (`type`),
    INDEX `idx_movements_created` (`created_at`),
    FOREIGN KEY (`product_id`) REFERENCES `inv_products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `auth_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Clients ─────────────────────────────────

CREATE TABLE IF NOT EXISTS `clients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_type` VARCHAR(20) NULL DEFAULT 'DNI',
    `document_number` VARCHAR(20) NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(100) NULL,
    `phone` VARCHAR(20) NULL,
    `address` TEXT NULL,
    `status` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_clients_document` (`document_number`),
    INDEX `idx_clients_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Sales ───────────────────────────────────

CREATE TABLE IF NOT EXISTS `sales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NULL,
    `user_id` INT NOT NULL,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('completada', 'anulada') NOT NULL DEFAULT 'completada',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sales_client` (`client_id`),
    INDEX `idx_sales_user` (`user_id`),
    INDEX `idx_sales_status` (`status`),
    INDEX `idx_sales_created` (`created_at`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `auth_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Sales: Items ────────────────────────────

CREATE TABLE IF NOT EXISTS `sales_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sale_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    INDEX `idx_sales_items_sale` (`sale_id`),
    INDEX `idx_sales_items_product` (`product_id`),
    FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `inv_products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Datos Iniciales ─────────────────────────

-- Roles base
INSERT INTO `rbac_roles` (`name`, `description`) VALUES
('admin', 'Administrador del sistema - acceso total'),
('almacenero', 'Encargado de almacen - gestiona inventario'),
('vendedor', 'Vendedor - punto de venta y clientes')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- Usuario admin por defecto (password: 12345678)
INSERT INTO `auth_users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'torresmatt37@gmail.com', '$2y$12$pHDlYjQwA2WU4bNIrSpCUeO1iSyDCSIn6.GmOsZ4UvZa19pv6c0Me', 'admin')
ON DUPLICATE KEY UPDATE `role` = 'admin';

-- Permisos base
INSERT INTO `rbac_permissions` (`name`, `slug`, `module`, `description`) VALUES
('Ver Dashboard', 'dashboard.view', 'dashboard', 'Acceso al panel principal'),
('Gestionar Roles', 'rbac.roles', 'rbac', 'Crear, editar y eliminar roles'),
('Gestionar Permisos', 'rbac.permissions', 'rbac', 'Asignar permisos a roles'),
('Gestionar Usuarios', 'rbac.users', 'rbac', 'Crear, editar y eliminar usuarios del sistema'),
('Ver Auditoria', 'rbac.audit', 'rbac', 'Ver registro de auditoria'),
('Ver Stock', 'inventory.stock', 'inventory', 'Ver tabla de stock'),
('Gestionar Productos', 'inventory.products', 'inventory', 'Crear, editar y eliminar productos'),
('Gestionar Proveedores', 'inventory.suppliers', 'inventory', 'Crear, editar y eliminar proveedores'),
('Ver Clientes', 'clients.view', 'clients', 'Ver listado de clientes'),
('Gestionar Clientes', 'clients.manage', 'clients', 'Crear, editar y eliminar clientes'),
('Punto de Venta', 'sales.pos', 'sales', 'Usar el punto de venta'),
('Ver Historial Ventas', 'sales.history', 'sales', 'Ver historial de ventas'),
('Ver Reportes', 'sales.reports', 'sales', 'Ver reportes de ventas')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- Asignar todos los permisos al rol admin
INSERT IGNORE INTO `rbac_role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `rbac_permissions`;

SET FOREIGN_KEY_CHECKS = 1;
