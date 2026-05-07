# Proyecto Bee - Sistema de Gestión

## Descripción

Sistema integral de gestión empresarial desarrollado en PHP 7. Incluye autenticación de usuarios, control de inventario, gestión de permisos (RBAC), administración de clientes y registro de ventas.

---

## Stack Tecnológico

| Capa | Tecnología |
|------|------------|
| Backend | PHP 7.4+ |
| Frontend | HTML5, CSS3, JavaScript (ES6) |
| Base de Datos | MySQL |
| Servidor | Apache (XAMPP) |

---

## Estructura de Carpetas

```
/assets
  /css           - Hojas de estilo globales y por módulo
  /js            - Scripts JavaScript globales y por módulo
  /images        - Recursos gráficos, logos, iconos

/includes
  /config        - Configuración (database.php, constants.php)
  /functions     - Funciones utilitarias globales
  /classes       - Clases PHP del core (Database, Session, Validator)

/modules
  /auth          - Login, registro, logout, recuperación
  /inventory     - Stock, productos CRUD, proveedores CRUD
  /rbac          - Permisos, roles, usuarios, auditoría
  /clients       - Clientes CRUD, historial
  /sales         - Punto de venta, facturación, reportes

/templates
  /partials      - Headers, footers, sidebars reutilizables

/database
  /migrations    - Scripts SQL de creación y migración

.htaccess        - Configuración de reescritura Apache
index.php        - Punto de entrada principal
```

---

## Convenciones de Código

### PHP

- Indentación: 4 espacios
- Nombres de clases: PascalCase (ej: `DatabaseConnection`)
- Nombres de métodos: camelCase (ej: `getUserById`)
- Nombres de variables: snake_case (ej: `$user_data`)
- Archivos de clase: mismo nombre que la clase (ej: `User.php`)
- Constantes: UPPER_SNAKE_CASE
- Comillas: preferir comillas simples para strings simples
- Apertura de llaves: misma línea para clases y métodos

```php
class DatabaseConnection {
    private $connection_string;
    
    public function connect() {
        // implementación
    }
}
```

### JavaScript

- Indentación: 4 espacios
- Nombres de funciones: camelCase
- Variables: camelCase (const para valores fijos, let para mutables)
- Punto y coma obligatorio al final de cada sentencia
- Preferir arrow functions para callbacks

### SQL

- Palabras clave en MAYÚSCULAS
- Nombres de tablas: snake_case, plural
- Nombres de columnas: snake_case
- Prefijos de tablas por módulo: `auth_users`, `inv_products`

### Nomenclatura de Archivos

- Controladores: `[modulo]_[accion].php` (ej: `auth_login.php`)
- Vistas: `[vista].view.php` (ej: `dashboard.view.php`)
- Assets: `[modulo].[tipo].css` (ej: `inventory.styles.css`)
- Includes: `[funcion].inc.php` (ej: `session.inc.php`)

---

## Reglas de Estilo CSS

### Variables Globales

Todas las hojas de estilo deben importar `/assets/css/variables.css` al inicio.

```css
@import url('variables.css');
```

### Paleta de Colores

| Variable | Valor | Uso |
|----------|-------|-----|
| `--color-primary` | #ffffff | Fondos principales, cards |
| `--color-secondary` | #fff475 | Acentos, highlights, badges |
| `--color-tertiary` | #6fff5c | Estados success, disponible, activo |
| `--color-text` | #2d2d2d | Texto principal, iconos |

### Variables Adicionales Requeridas

```css
:root {
    /* Colores base */
    --color-primary: #ffffff;
    --color-secondary: #fff475;
    --color-tertiary: #6fff5c;
    --color-text: #2d2d2d;
    
    /* Estados */
    --color-success: #6fff5c;
    --color-warning: #fff475;
    --color-error: #ff6b6b;
    --color-info: #6c9eff;
    
    /* Neutros */
    --color-bg: #f5f5f5;
    --color-border: #e0e0e0;
    --color-muted: #9e9e9e;
    
    /* Tipografía */
    --font-base: 'Segoe UI', system-ui, sans-serif;
    --font-size-xs: 0.75rem;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.25rem;
    --font-size-xl: 1.5rem;
    --font-size-2xl: 2rem;
    
    /* Espaciado */
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    
    /* Layout */
    --sidebar-width: 260px;
    --header-height: 60px;
    --border-radius: 8px;
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
}
```

---

## Descripción de Módulos

### 1. Auth (Autenticación)

**Responsabilidad**: Gestionar el acceso al sistema

**Funcionalidades**:
- Login con usuario/email y contraseña
- Registro de nuevos usuarios
- Cierre de sesión
- Recuperación de contraseña
- Verificación de sesión activa

**Archivos esperados**:
- `modules/auth/login.php`
- `modules/auth/register.php`
- `modules/auth/logout.php`
- `modules/auth/forgot-password.php`

### 2. Inventory (Inventario)

**Responsabilidad**: Control de stock y gestión de productos

**Funcionalidades**:
- Tabla de stock con filtros y búsqueda
- CRUD de productos (nombre, descripción, precio, cantidad, proveedor)
- CRUD de proveedores
- Alertas de stock bajo
- Historial de movimientos

**Archivos esperados**:
- `modules/inventory/stock.php`
- `modules/inventory/products.php`
- `modules/inventory/products-form.php`
- `modules/inventory/suppliers.php`
- `modules/inventory/suppliers-form.php`

### 3. RBAC (Control de Acceso Basado en Roles)

**Responsabilidad**: Seguridad, permisos y auditoría

**Funcionalidades**:
- Gestión de roles (admin, vendedor, almacenero)
- Asignación de permisos por rol
- CRUD de usuarios del sistema
- Registro de acciones (log de auditoría)
- Validación de permisos en cada operación

**Archivos esperados**:
- `modules/rbac/roles.php`
- `modules/rbac/permissions.php`
- `modules/rbac/users.php`
- `modules/rbac/audit-log.php`

### 4. Clients (Clientes)

**Responsabilidad**: Administración de la cartera de clientes

**Funcionalidades**:
- CRUD de clientes (nombre, contacto, dirección, documento)
- Búsqueda de clientes
- Historial de compras por cliente
- Estados del cliente (activo, inactivo)

**Archivos esperados**:
- `modules/clients/list.php`
- `modules/clients/form.php`
- `modules/clients/detail.php`

### 5. Sales (Ventas)

**Responsabilidad**: Gestión de transacciones comerciales

**Funcionalidades**:
- Punto de venta (selección de productos, cantidad, precio)
- Asociación de venta con cliente
- Generación de comprobantes
- Historial de ventas
- Reportes de ventas por período

**Archivos esperados**:
- `modules/sales/pos.php`
- `modules/sales/history.php`
- `modules/sales/receipt.php`
- `modules/sales/reports.php`

---

## Patrones de Arquitectura

### 1. Estructura de Módulos

Cada módulo sigue esta estructura interna:

```
/modules/[modulo]/
  index.php           - Listado principal o dashboard del módulo
  [accion].php        - Controladores específicos
  /api                - Endpoints AJAX
  /assets
    /css              - Estilos específicos del módulo
    /js               - Scripts específicos del módulo
```

### 2. Separación de Responsabilidades

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Presentación  │────▶│    Lógica       │────▶│    Datos        │
│   (Vistas)      │     │   (Controladores)│     │   (Modelos)     │
└─────────────────┘     └─────────────────┘     └─────────────────┘
        ▲                                               │
        └───────────────────────────────────────────────┘
                    (Retorno de datos)
```

### 3. Flujo de Request

```
index.php → includes/config/ → includes/classes/ → modules/[modulo]/
    │              │                    │                   │
    ▼              ▼                    ▼                   ▼
Inicializar    Configuración       Autenticación      Controlador
app            base de datos       y permisos         específico
```

### 4. Convenciones de Base de Datos

- Conexión centralizada en `includes/config/database.php`
- Consultas preparadas obligatorias
- Cada módulo puede tener su propio archivo de consultas en `includes/functions/[modulo].functions.php`

### 5. Reutilización de Componentes

- Templates comunes en `/templates/partials/`
- Header: `header.php`
- Footer: `footer.php`
- Sidebar de navegación: `sidebar.php`
- Mensajes de alerta: `alert.php`

### 6. Seguridad

- Validación de sesión en cada módulo protegido
- CSRF tokens en formularios
- Sanitización de inputs con `htmlspecialchars()`
- Consultas preparadas para prevenir SQL injection
- Validación de permisos RBAC antes de cada operación

---

## Configuración Inicial

### Requisitos del Servidor

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones: PDO, mysqli, mbstring

### Instalación

1. Clonar repositorio en `C:/XAMPP/htdocs/bee/`
2. Crear base de datos `bee_system`
3. Ejecutar migraciones: `database/migrations/`
4. Configurar credenciales en `includes/config/database.php`
5. Acceder a `http://localhost/bee/`

---

## Versionado

Formato: `MAJOR.MINOR.PATCH`

- MAJOR: Cambios incompatibles con versiones anteriores
- MINOR: Nuevas funcionalidades manteniendo compatibilidad
- PATCH: Correcciones de errores

Versión actual: 1.0.0
