# Sistema Multi-Tenant con Bases de Datos Separadas

## 📋 Resumen del Sistema

Este sistema ha sido corregido para implementar **multi-tenancy real** donde:

✅ **Base de datos central** guarda información de tenants y subdominios
✅ **Cada tenant** tiene su propia base de datos exclusiva
✅ **Automáticamente** crea BD y ejecuta migraciones al registrar tenant
✅ **Middleware** identifica tenant por subdominio y conecta a su BD
✅ **Aislamiento completo** de datos entre tenants

## 🔧 Configuración Requerida

### 1. Variables de Entorno (.env)

```bash
# Base de datos principal (para gestión de tenants)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_taller
DB_USERNAME=root
DB_PASSWORD=tu_password_mysql

# Configuración para tenants (usa las mismas credenciales)
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306

# Dominio base para subdominios
BASE_DOMAIN=ec2-18-219-51-191.us-east-2.compute.amazonaws.com
```

### 2. Permisos MySQL Necesarios

El usuario MySQL debe tener permisos para:
- Crear bases de datos
- Crear usuarios
- Otorgar permisos

```sql
-- Como root en MySQL:
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

## 🚀 Instalación y Configuración

### 1. Ejecutar Migración Central

```bash
# Migrar tabla de tenants en BD central
php artisan migrate --path=database/migrations/2025_09_24_182508_create_tenants_table.php
```

### 2. Crear Primer Tenant

```bash
# Crear tenant desde consola
php artisan tenant:create empresa1 "Empresa Uno" empresa1 --domain=empresa1.com

# O desde API POST /api/admin/tenants
{
    "tenant_id": "empresa1",
    "name": "Empresa Uno",
    "subdomain": "empresa1",
    "domain": "empresa1.com",
    "settings": {}
}
```

## 📁 Estructura de Archivos Implementada

```
app/
├── Models/
│   ├── Tenant.php               # ✅ Modelo principal de tenant
│   ├── Usuario.php              # ✅ Actualizado (sin TenantScope)
│   └── Tarea.php                # ✅ Actualizado (sin TenantScope)
├── Http/
│   ├── Controllers/
│   │   └── TenantController.php # ✅ API para gestión de tenants
│   └── Middleware/
│       └── IdentifyTenant.php   # ✅ Middleware actualizado para BD separadas
└── Console/Commands/
    └── CreateTenantCommand.php  # ✅ Comando Artisan para crear tenants

database/
├── migrations/
│   └── 2025_09_24_182508_create_tenants_table.php # ✅ Migración central
└── migrations/tenant/          # ✅ Migraciones específicas de tenant
    ├── 2025_01_01_000001_create_usuarios_table.php
    ├── 2025_01_01_000002_create_tareas_table.php
    └── 2025_01_01_000003_create_personal_access_tokens_table.php

routes/api.php                  # ✅ Rutas de gestión de tenants agregadas
```

## 🔄 Flujo de Funcionamiento

### 1. Creación de Tenant

```php
// Proceso automático al crear tenant:
1. Validar datos únicos (tenant_id, subdomain)
2. Generar credenciales BD únicas
3. Crear entrada en BD central (tabla tenants)
4. Crear BD física con nombre: tenant_{tenant_id}
5. Crear usuario MySQL específico para el tenant
6. Ejecutar migraciones tenant en la nueva BD
7. Confirmar transacción
```

### 2. Acceso por Subdominio

```php
// Middleware IdentifyTenant:
1. Extraer subdominio de URL (ej: empresa1.dominio.com)
2. Buscar tenant en BD central por subdomain
3. Obtener credenciales encriptadas del tenant
4. Configurar conexión dinámica a BD del tenant
5. Establecer contexto tenant en aplicación
6. Continuar request con BD del tenant activa
```

### 3. Aislamiento de Datos

```php
// Cada tenant trabajará únicamente con:
- Su propia base de datos: tenant_empresa1, tenant_empresa2, etc.
- Sus propios usuarios MySQL
- Sus propias tablas y datos
- Migraciones independientes
```

## 🛠️ API Endpoints

### Gestión de Tenants (Solo Dominio Principal)

```http
# Crear tenant
POST /api/admin/tenants
Content-Type: application/json

{
    "tenant_id": "empresa2",
    "name": "Empresa Dos",
    "subdomain": "empresa2"
}

# Listar tenants
GET /api/admin/tenants

# Ver tenant específico
GET /api/admin/tenants/empresa1

# Actualizar tenant
PUT /api/admin/tenants/empresa1

# Eliminar tenant (y su BD)
DELETE /api/admin/tenants/empresa1
```

### APIs de Tenant (Con Subdominio)

```http
# Desde empresa1.dominio.com
GET /api/usuarios/listUsers     # Solo usuarios de empresa1
POST /api/tareas               # Solo tareas de empresa1
```

## 🔒 Seguridad Implementada

✅ **Validación de subdominios** - Solo alfanuméricos, guiones y guiones bajos
✅ **Subdominios reservados** - www, api, admin, root, etc. bloqueados
✅ **Contraseñas encriptadas** - Credenciales de BD encriptadas en BD central
✅ **Aislamiento completo** - Imposible acceso cruzado entre tenants
✅ **Logs de auditoría** - Registro de creación/acceso de tenants
✅ **Transacciones BD** - Rollback automático en caso de error

## 🧪 Testing

### 1. Crear Tenant de Prueba

```bash
php artisan tenant:create test "Tenant Test" test
```

### 2. Verificar Creación

```bash
# Verificar BD creada
mysql -u root -p -e "SHOW DATABASES LIKE 'tenant_test';"

# Verificar tablas del tenant
mysql -u root -p tenant_test -e "SHOW TABLES;"
```

### 3. Probar APIs

```bash
# Desde dominio principal - gestión
curl -X POST http://dominio.com/api/admin/tenants \
  -H "Content-Type: application/json" \
  -d '{"tenant_id":"test2","name":"Test 2","subdomain":"test2"}'

# Desde subdominio - datos del tenant
curl -X GET http://test.dominio.com/api/usuarios/listUsers
```

## ⚠️ Consideraciones de Producción

### 1. Recursos del Servidor
- Cada tenant = 1 BD adicional
- Monitorear uso de memoria/CPU
- Considerar límites por servidor

### 2. Backup y Restauración
- Backup separado por cada BD de tenant
- Scripts de restauración por tenant
- Backup de BD central crítico

### 3. Escalabilidad
- Para muchos tenants considerar cluster de BD
- Balanceador de carga por subdominio
- Cache Redis por tenant

### 4. Monitoreo
- Logs por tenant separados
- Métricas de uso por tenant
- Alertas de recursos por BD

## 🔧 Troubleshooting

### Error: "Access denied for user"
```bash
# Verificar permisos MySQL
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

### Error: "Tenant no encontrado"
```bash
# Verificar BD central
php artisan migrate --path=database/migrations/2025_09_24_182508_create_tenants_table.php
```

### Error: "Database does not exist"
```bash
# Recrear tenant
php artisan tenant:create {tenant_id} "{name}" {subdomain}
```

---

## 📞 Próximos Pasos

1. **Configurar credenciales MySQL correctas**
2. **Ejecutar migración de tabla tenants**
3. **Crear primer tenant de prueba**
4. **Verificar funcionamiento con subdominios**
5. **Desplegar en AWS EC2**