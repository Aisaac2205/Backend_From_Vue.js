# 🏢 SISTEMA MULTITENANT - ARQUITECTURA Y DEMOSTRACIÓN

## ⚠️ **IMPORTANTE - SOLO DEMOSTRACIÓN ACADÉMICA**

Este documento y las clases incluidas son **ÚNICAMENTE PARA DEMOSTRACIÓN** de cómo funcionaría un sistema multitenant en Laravel. **NO están implementadas en el sistema actual** y **NO afectan la funcionalidad existente**.

---

## 📋 **Definición de Multitenancy**

**Multitenancy** es un modelo de arquitectura en el que una misma aplicación puede servir a múltiples clientes (tenants), manteniendo aislados sus datos. En este caso, cada subdominio debe apuntar a un esquema de base de datos diferente.

### **Ejemplo Práctico:**
- `empresa1.midominio.com` → Base de datos: `tenant_empresa1`
- `empresa2.midominio.com` → Base de datos: `tenant_empresa2`
- `empresa3.midominio.com` → Base de datos: `tenant_empresa3`

Los usuarios de cada empresa solo ven sus propios datos.

---

## 🏗️ **ARQUITECTURA DEL SISTEMA MULTITENANT**

### **1. Componentes Principales**

#### **A. Modelo Tenant (`app/Models/Tenant.php`)**
```php
class Tenant extends Model
{
    // Gestiona la información de cada inquilino
    protected $fillable = [
        'nombre',           // Nombre de la empresa
        'subdominio',       // empresa1, empresa2, etc.
        'database_name',    // tenant_empresa1
        'database_host',    // Servidor de BD
        'database_username', // Usuario BD
        'database_password', // Contraseña BD
        'estado',           // Activo/Inactivo
        'configuracion',    // JSON con configs específicas
        'fecha_expiracion'  // Fecha de expiración
    ];
}
```

#### **B. Middleware TenantResolver (`app/Http/Middleware/TenantResolver.php`)**
```php
class TenantResolver
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Extraer subdominio de la URL
        $host = $request->getHost(); // empresa1.midominio.com
        $subdomain = explode('.', $host)[0]; // empresa1
        
        // 2. Buscar tenant en BD maestra
        $tenant = Tenant::findBySubdomain($subdomain);
        
        // 3. Configurar conexión a BD del tenant
        $this->setTenantDatabase($tenant);
        
        // 4. Continuar con la petición
        return $next($request);
    }
}
```

#### **C. Servicio TenantService (`app/Services/TenantService.php`)**
```php
class TenantService
{
    // Gestiona la lógica central del sistema multitenant
    public function setDatabaseConnection(Tenant $tenant);
    public function createTenant($data);
    public function deleteTenant(Tenant $tenant);
    public function getCurrentTenant();
}
```

#### **D. Trait TenantScope (`app/Traits/TenantScope.php`)**
```php
trait TenantScope
{
    // Se aplicaría a modelos Usuario y Tarea para filtrar automáticamente
    protected static function bootTenantScope()
    {
        // Agregar tenant_id automáticamente a consultas
        static::addGlobalScope('tenant', function (Builder $builder) {
            $builder->where('tenant_id', getCurrentTenantId());
        });
    }
}
```

---

## 🔄 **FLUJO DE FUNCIONAMIENTO**

### **1. Resolución de Tenant por Subdominio**

```
1. Usuario accede a: empresa1.midominio.com/login
   ↓
2. Middleware TenantResolver extrae: "empresa1"
   ↓
3. Busca en BD maestra: SELECT * FROM tenants WHERE subdominio = 'empresa1'
   ↓
4. Obtiene configuración de BD: tenant_empresa1
   ↓
5. Configura conexión: database.connections.tenant_empresa1
   ↓
6. Todas las consultas van a: tenant_empresa1
```

### **2. Aislamiento de Datos**

```
Tenant "empresa1":
├── usuarios (solo de empresa1)
├── tareas (solo de empresa1)
└── reportes (solo de empresa1)

Tenant "empresa2":
├── usuarios (solo de empresa2)
├── tareas (solo de empresa2)
└── reportes (solo de empresa2)
```

### **3. Estructura de Base de Datos**

#### **Base de Datos Maestra:**
```sql
-- Contiene información de todos los tenants
CREATE DATABASE gestor_tareas_master;
USE gestor_tareas_master;

CREATE TABLE tenants (
    id INT PRIMARY KEY,
    nombre VARCHAR(255),
    subdominio VARCHAR(50) UNIQUE,
    database_name VARCHAR(255),
    database_host VARCHAR(255),
    database_username VARCHAR(255),
    database_password VARCHAR(255),
    estado BOOLEAN,
    configuracion JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### **Base de Datos de Tenant:**
```sql
-- Una BD independiente por cada tenant
CREATE DATABASE tenant_empresa1;
USE tenant_empresa1;

CREATE TABLE usuarios (
    id INT PRIMARY KEY,
    nombre VARCHAR(255),
    email VARCHAR(255),
    password VARCHAR(255),
    rol ENUM('admin', 'usuario'),
    -- NO necesita tenant_id porque toda la BD es del tenant
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE tareas (
    id INT PRIMARY KEY,
    titulo VARCHAR(255),
    descripcion TEXT,
    estado ENUM('pendiente', 'en_progreso', 'completada'),
    usuario_id INT,
    -- NO necesita tenant_id porque toda la BD es del tenant
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🚀 **IMPLEMENTACIÓN HIPOTÉTICA**

### **1. Configuración de Rutas**
```php
// routes/api.php
Route::middleware(['tenant.resolve'])->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index']);
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::get('/tareas', [TareaController::class, 'index']);
        Route::post('/tareas', [TareaController::class, 'store']);
    });
});
```

### **2. Modificación de Modelos (Hipotética)**
```php
// Si se implementara, los modelos se modificarían así:
class Usuario extends Authenticatable
{
    use TenantScope; // Filtrado automático por tenant
    
    // El resto del código permanece igual
}

class Tarea extends Model  
{
    use TenantScope; // Filtrado automático por tenant
    
    // El resto del código permanece igual
}
```

### **3. Creación de Nuevo Tenant**
```php
$tenantService = new TenantService();

$tenant = $tenantService->createTenant([
    'nombre' => 'Empresa Ejemplo S.A.',
    'subdominio' => 'empresa-ejemplo',
    'database_username' => 'tenant_user',
    'database_password' => 'secure_password',
]);

// Esto crearía:
// - Registro en tabla tenants
// - Base de datos: tenant_empresa-ejemplo  
// - Ejecutar migraciones en la nueva BD
// - Crear usuario admin inicial
```

---

## 🔒 **SEGURIDAD Y AISLAMIENTO**

### **1. Aislamiento de Base de Datos**
- **Física:** Cada tenant tiene su propia base de datos
- **Completa:** Imposible acceso cruzado entre tenants
- **Escalable:** Bases de datos pueden estar en servidores diferentes

### **2. Validaciones de Seguridad**
```php
// Middleware verificaría que el usuario pertenece al tenant
public function handle(Request $request, Closure $next)
{
    $tenant = $this->resolveTenant($request);
    $user = $request->user();
    
    if (!$this->userBelongsToTenant($user, $tenant)) {
        return response()->json(['error' => 'Acceso denegado'], 403);
    }
    
    return $next($request);
}
```

### **3. Prevención de Accesos Cruzados**
- Middleware valida tenant en cada petición
- Scopes automáticos filtran datos por tenant
- Logs de seguridad para accesos sospechosos

---

## 📊 **VENTAJAS DEL SISTEMA MULTITENANT**

### **1. Aislamiento Completo**
- ✅ Datos completamente separados
- ✅ Configuraciones independientes
- ✅ Escalabilidad individual

### **2. Gestión Centralizada**
- ✅ Una aplicación para múltiples clientes
- ✅ Actualizaciones centralizadas
- ✅ Monitoreo unificado

### **3. Flexibilidad**
- ✅ Personalización por cliente
- ✅ Planes de precios diferentes
- ✅ Bases de datos en servidores diferentes

---

## 🛠️ **ARCHIVOS DE DEMOSTRACIÓN INCLUIDOS**

### **Backend Laravel:**
1. **`app/Models/Tenant.php`** - Modelo principal del tenant
2. **`app/Http/Middleware/TenantResolver.php`** - Resolución de tenant por subdominio
3. **`app/Services/TenantService.php`** - Lógica central del sistema
4. **`app/Http/Controllers/Api/TenantController.php`** - API para gestión de tenants
5. **`app/Traits/TenantScope.php`** - Filtrado automático por tenant
6. **`config/multitenant_demo.php`** - Configuración del sistema
7. **`database/migrations/...create_tenants_table_demo.php`** - Estructura de tabla

### **Estructura Propuesta:**
```
backend/
├── app/
│   ├── Models/
│   │   └── Tenant.php ✅
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   └── TenantController.php ✅
│   │   └── Middleware/
│   │       └── TenantResolver.php ✅
│   ├── Services/
│   │   └── TenantService.php ✅
│   └── Traits/
│       └── TenantScope.php ✅
├── config/
│   └── multitenant_demo.php ✅
└── database/
    └── migrations/
        └── 2024_09_28_000000_create_tenants_table_demo.php ✅
```

---

## 🎯 **RESUMEN PARA PRESENTACIÓN**

### **¿Qué es Multitenancy?**
Un modelo donde una aplicación sirve a múltiples clientes manteniendo sus datos completamente aislados.

### **¿Cómo Funciona?**
1. **Subdominio** → empresa1.midominio.com
2. **Resolución** → Buscar tenant "empresa1"
3. **Conexión** → Cambiar a base de datos tenant_empresa1
4. **Aislamiento** → Solo datos de empresa1 son accesibles

### **Beneficios:**
- ✅ **Aislamiento completo** de datos
- ✅ **Escalabilidad** por cliente
- ✅ **Gestión centralizada** de la aplicación
- ✅ **Personalización** individual

### **Implementación:**
- 🏗️ **Arquitectura preparada** con clases de demostración
- 🔒 **Seguridad planificada** con middleware y validaciones
- 📊 **Escalabilidad diseñada** para múltiples bases de datos

---

**NOTA FINAL:** Todas las clases y archivos incluidos son únicamente para **DEMOSTRACIÓN ACADÉMICA**. El sistema actual continúa funcionando normalmente sin implementación multitenant real.