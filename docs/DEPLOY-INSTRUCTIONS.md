# 🚀 Instrucciones de Despliegue en AWS EC2

## 📋 Resumen

Este proyecto está **listo para desplegar** en AWS EC2 con sistema multi-tenant completo usando bases de datos separadas por tenant.

## 🎯 Lo que está incluido en este ZIP:

✅ **Sistema Multi-Tenant** - Cada tenant tiene su propia base de datos
✅ **API completa** - Usuarios, tareas, autenticación con Sanctum
✅ **Seguridad mejorada** - Headers, validaciones, rate limiting
✅ **Configuración de producción** - Apache, SSL, optimizaciones
✅ **Scripts de instalación** - Automatizado para EC2
✅ **Documentación completa** - MULTITENANCY.md

## 🚀 Pasos para Desplegar en EC2:

### 1. **Subir archivos al servidor**

```bash
# En tu EC2 (como root o con sudo):
cd /tmp
# Subir laravel-api.zip y extraer
unzip laravel-api.zip
```

### 2. **Ejecutar instalación automática**

```bash
# Ejecutar script de instalación (como root):
sudo bash /tmp/laravel-api/install-ec2.sh
```

Este script **automáticamente**:
- ✅ Instala PHP 8.1, Apache, MariaDB
- ✅ Configura base de datos con contraseña admin123
- ✅ Instala dependencias de Composer
- ✅ Configura variables de entorno de producción
- ✅ Ejecuta migraciones de BD central y tenants
- ✅ Configura Apache con subdominios
- ✅ Aplica configuraciones de seguridad
- ✅ Crea tenant de ejemplo

### 3. **Verificar instalación**

```bash
# URLs para probar:
curl http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/admin/tenants
curl http://demo.ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/usuarios/listUsers
```

## 🔧 Configuración Manual (si prefieres)

Si prefieres configurar manualmente:

### 1. **Instalar dependencias**
```bash
apt update && apt upgrade -y
apt install -y php8.1 php8.1-cli php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip
apt install -y apache2 mariadb-server composer
```

### 2. **Configurar MariaDB**
```bash
systemctl start mariadb
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'admin123';"
mysql -u root -padmin123 -e "CREATE DATABASE laravel_taller;"
```

### 3. **Configurar aplicación**
```bash
cp -r laravel-api /var/www/
cd /var/www/laravel-api
cp .env.production.ec2 .env
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
```

### 4. **Configurar Apache**
```bash
cp apache-config.conf /etc/apache2/sites-available/laravel-multitenancy.conf
a2ensite laravel-multitenancy
a2dissite 000-default
systemctl reload apache2
```

## 🏗️ Arquitectura del Sistema

```
🌐 Dominio Principal: ec2-18-219-51-191.us-east-2.compute.amazonaws.com
├── /api/admin/tenants (Gestión de tenants)
├── /api/auth (Autenticación global)
└── BD Central: laravel_taller

🏪 Subdominios de Tenants:
├── empresa1.ec2-18-219-51-191.us-east-2.compute.amazonaws.com
│   ├── /api/usuarios (Solo usuarios de empresa1)
│   ├── /api/tareas (Solo tareas de empresa1)
│   └── BD: tenant_empresa1
├── empresa2.ec2-18-219-51-191.us-east-2.compute.amazonaws.com
│   └── BD: tenant_empresa2
└── demo.ec2-18-219-51-191.us-east-2.compute.amazonaws.com
    └── BD: tenant_demo
```

## 🔑 APIs Principales

### 👤 **Usuarios por Defecto en Cada Tenant:**
```
🔐 Administrador: admin@admin.com / admin123
👤 Usuario Demo: usuario@demo.com / demo123  
✏️ Editor Demo: editor@demo.com / editor123
```

### Gestión de Tenants (Dominio Principal)
```bash
# Crear tenant
POST /api/admin/tenants
{
    "tenant_id": "empresa1",
    "name": "Empresa Uno",
    "subdomain": "empresa1"
}

# Listar tenants
GET /api/admin/tenants

# Ver tenant específico
GET /api/admin/tenants/empresa1
```

### APIs de Tenant (Subdominio)
```bash
# Desde empresa1.dominio.com
POST /api/auth/login
{
    "email": "admin@admin.com",
    "password": "admin123"
}

GET /api/usuarios/listUsers (requiere auth)
POST /api/usuarios/addUser (requiere rol admin)
POST /api/tareas (requiere auth)
```

## 🔒 Seguridad Implementada

✅ **Aislamiento completo** - Cada tenant solo ve sus datos
✅ **Headers de seguridad** - XSS, CSRF, Content-Type protection
✅ **Rate limiting** - 5 intentos de login por minuto
✅ **Validación de subdominios** - Solo alfanuméricos permitidos
✅ **Credenciales encriptadas** - Contraseñas de BD encriptadas
✅ **Logs de auditoría** - Registro de accesos y errores

## 🧪 Testing Post-Instalación

### 1. **Crear tenant de prueba**
```bash
php artisan tenant:create test "Test Company" test
```

### 2. **Verificar BD creadas**
```bash
mysql -u root -padmin123 -e "SHOW DATABASES LIKE 'tenant_%';"
```

### 3. **Probar APIs**
```bash
# Crear usuario en tenant
curl -X POST http://test.dominio.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@test.com","password":"password123"}'
```

## 📞 Soporte y Troubleshooting

### Logs importantes:
- Laravel: `/var/www/laravel-api/storage/logs/laravel.log`
- Apache: `/var/log/apache2/laravel_error.log`
- MariaDB: `/var/log/mysql/error.log`

### Comandos útiles:
```bash
# Ver tenants creados
php artisan tinker --execute="App\Models\Tenant::all()"

# Crear tenant manual
php artisan tenant:create empresa2 "Empresa Dos" empresa2

# Verificar configuración
php artisan config:show database

# Limpiar cache
php artisan optimize:clear
```

## 🎉 ¡Listo para Producción!

Este sistema está completamente preparado para producción con:
- Multi-tenancy real con BD separadas
- Seguridad robusta
- Configuración optimizada
- Documentación completa
- Scripts de instalación automatizados

**¡Solo sube el ZIP y ejecuta el script de instalación!** 🚀