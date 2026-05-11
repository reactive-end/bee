# Bee - Sistema de Gestión

Sistema integral de gestión empresarial desarrollado en PHP 7.4+ con MySQL. Incluye autenticación, control de inventario, gestión de permisos (RBAC), administración de clientes y punto de venta.

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior (recomendado 8.0+)
- Apache con mod_rewrite (Laragon, XAMPP, etc.)
- Extensiones PHP: PDO, mysqli, mbstring

## Instalación

1. Clonar el repositorio en la raíz web (ej: `C:/Laragon/www/bee/`)
2. Crear la base de datos ejecutando la migración:

```sql
mysql -u root < database/migrations/001_initial_schema.sql
```

3. Verificar credenciales en `includes/config/database.php`
4. Acceder a `http://localhost/bee/`

## Acceso inicial

| Campo | Valor |
|---|---|
| **Usuario** | admin |
| **Email** | torresmatt37@gmail.com |
| **Contraseña** | 12345678 |
| **Rol** | admin (acceso total) |

## Módulos

| Módulo | Descripción |
|---|---|
| **Dashboard** | Panel principal con estadísticas y accesos rápidos |
| **Inventario** | Stock, CRUD de productos, proveedores, alertas de stock bajo |
| **Clientes** | Registro de clientes, historial de compras, estados |
| **Ventas** | Punto de venta (POS), historial, comprobantes, reportes exportables |
| **RBAC** | Roles, permisos por módulo, gestión de usuarios, auditoría |

## Estructura

```
/
├── assets/              # CSS, JS, imágenes
│   ├── css/             # variables.css, app.css, login.css
│   └── js/              # app.js, login.js, gsap.min.js
├── includes/
│   ├── classes/         # Database.php, Session.php
│   ├── config/          # database.php, constants.php
│   └── functions/       # Funciones por módulo
├── modules/
│   ├── auth/            # Login, logout
│   ├── dashboard/       # Panel principal
│   ├── inventory/       # Stock, productos, proveedores
│   ├── rbac/            # Roles, permisos, usuarios, auditoría
│   ├── clients/         # Listado, formulario, detalle
│   └── sales/           # POS, historial, comprobante, reportes
├── templates/
│   └── partials/        # Header, footer, sidebar, layout
├── database/
│   └── migrations/      # Scripts SQL
├── index.php            # Punto de entrada
└── .htaccess            # Reglas Apache
```

## Exportación de reportes

El módulo de ventas permite exportar reportes en dos formatos:

- **Excel**: descarga archivo CSV compatible con Excel
- **PDF**: abre vista imprimible con disparo automático de impresión

## Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | PHP 7.4+ |
| Base de datos | MySQL |
| Frontend | HTML5, CSS3, JavaScript (ES6) |
| Animaciones | GSAP 3.12 (local) |
