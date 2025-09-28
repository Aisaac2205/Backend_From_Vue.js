# 📋 Gestor de Tareas - Backend API

Una aplicación completa de gestión de tareas construida con **Laravel 10** que proporciona una API REST robusta para manejar usuarios, tareas y autenticación.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange?style=flat&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green?style=flat)

## 🚀 Características Principales

- **🔐 Autenticación completa** con Laravel Sanctum
- **👥 Gestión de usuarios** con roles (admin/usuario)
- **📝 CRUD completo de tareas** con estados y fechas de vencimiento
- **🔒 API REST segura** con autenticación por tokens
- **📊 Exportación a Excel** de reportes de tareas
- **🌐 CORS configurado** para integración con frontend
- **🔧 Arquitectura escalable** con patrones MVC

## 🛠️ Stack Tecnológico

### Backend Core
- **Laravel 10.x** - Framework principal
- **PHP 8.1+** - Lenguaje de programación
- **Laravel Sanctum** - Autenticación de API
- **Eloquent ORM** - Mapeo objeto-relacional

### Dependencias Principales
```json
{
  "laravel/framework": "^10.10",
  "laravel/sanctum": "^3.2",
  "phpoffice/phpspreadsheet": "^5.1",
  "guzzlehttp/guzzle": "^7.2"
}
```

### Base de Datos
- **MySQL/MariaDB** - Base de datos principal
- **Migraciones de Laravel** - Control de versiones de BD
- **Seeders** - Datos de prueba

## 📁 Estructura del Proyecto

```
backend/
├── app/
│   ├── Http/Controllers/Api/     # Controladores de API
│   │   ├── AuthController.php    # Autenticación (login/register/logout)
│   │   ├── UsuarioController.php # Gestión de usuarios
│   │   ├── TareaController.php   # CRUD de tareas + Excel export
│   │   └── TenantController.php  # Multi-tenancy (futuro)
│   │
│   ├── Models/                   # Modelos Eloquent
│   │   ├── Usuario.php          # Modelo de usuario con roles
│   │   ├── Tarea.php            # Modelo de tarea con estados
│   │   └── Tenant.php           # Multi-tenancy
│   │
│   ├── Services/                # Lógica de negocio
│   └── Traits/                  # Funcionalidades reutilizables
│
├── database/
│   ├── migrations/              # Esquemas de base de datos
│   │   ├── create_usuarios_table.php    # Tabla usuarios
│   │   ├── create_tareas_table.php      # Tabla tareas
│   │   └── create_personal_access_tokens_table.php
│   └── seeders/                 # Datos de prueba
│
├── routes/
│   ├── api.php                  # Rutas de API REST
│   └── web.php                  # Rutas web (SPA)
│
├── config/                      # Configuraciones
├── public/                      # Assets públicos + SPA
├── storage/                     # Logs y archivos
└── tests/                       # Pruebas unitarias
```

## 🗄️ Modelo de Datos

### 👤 Usuarios (usuarios)
```php
- id (bigint, PK)
- nombre (string, 150)
- email (string, 150, unique)
- password (string, hashed)
- rol (enum: 'admin', 'usuario')
- created_at, updated_at
```

### 📋 Tareas (tareas)
```php
- id (bigint, PK)
- titulo (string, 200)
- descripcion (text, nullable)
- estado (enum: 'pendiente', 'en_progreso', 'completada')
- fecha_vencimiento (date, nullable)
- user_id (bigint, FK -> usuarios.id)
- created_at, updated_at
```

### 🔑 Tokens de Acceso (personal_access_tokens)
```php
- id, tokenable_type, tokenable_id
- name, token (unique)
- abilities, last_used_at
- expires_at, created_at, updated_at
```

## 🔌 API Endpoints

### 🔐 Autenticación
```http
POST /api/register          # Registro de usuario
POST /api/login             # Iniciar sesión
POST /api/logout            # Cerrar sesión
GET  /api/user              # Obtener usuario autenticado
GET  /api/verify-token      # Verificar validez del token
```

### 👥 Usuarios (requiere autenticación)
```http
GET    /api/usuarios         # Listar usuarios
POST   /api/usuarios         # Crear usuario
GET    /api/usuarios/{id}    # Obtener usuario específico
PUT    /api/usuarios/{id}    # Actualizar usuario
DELETE /api/usuarios/{id}    # Eliminar usuario
```

### 📝 Tareas (requiere autenticación)
```http
GET    /api/tareas                    # Listar tareas
POST   /api/tareas                    # Crear tarea
GET    /api/tareas/{id}               # Obtener tarea específica
PUT    /api/tareas/{id}               # Actualizar tarea
DELETE /api/tareas/{id}               # Eliminar tarea
PATCH  /api/tareas/{id}/status        # Cambiar estado de tarea
GET    /api/tareas/reporte-excel      # Exportar a Excel
```

### 🧪 Testing
```http
GET /api/test               # Endpoint de prueba (sin auth)
```

## ⚙️ Configuración y Despliegue

### Requisitos del Sistema
- PHP 8.1 o superior
- Composer 2.x
- MySQL 8.0+ o MariaDB 10.3+
- Apache 2.4+ o Nginx
- Node.js 18+ (para assets frontend)

### Variables de Entorno (.env)
```env
APP_NAME="Gestor de Tareas"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_taller
DB_USERNAME=root
DB_PASSWORD=your-password

SANCTUM_STATEFUL_DOMAINS=your-domain.com
SESSION_DOMAIN=your-domain.com
```

### 🚀 Scripts de Despliegue

#### Setup para EC2 (setup-ec2.sh)
```bash
chmod +x setup-ec2.sh
sudo ./setup-ec2.sh
```
**Funciones:**
- Configura permisos de archivos
- Instala dependencias de Composer y npm
- Ejecuta migraciones y seeders
- Configura Apache virtual host
- Limpia caché de Laravel

#### Restauración de Dependencias
```bash
# Windows PowerShell
./restore-dependencies.ps1

# Linux/Mac
./restore-dependencies.sh
```

## 🔐 Seguridad

### Autenticación
- **Laravel Sanctum** para tokens de API
- **Hash bcrypt** para contraseñas
- **CORS** configurado para dominios específicos
- **Rate limiting** en rutas de API

### Autorización
- **Middleware de autenticación** en rutas protegidas
- **Roles de usuario** (admin/usuario)
- **Validación de entrada** en todos los endpoints
- **Sanitización** de datos de salida

## 📊 Características Avanzadas

### Exportación de Datos
- **PHPSpreadsheet** para generar reportes Excel
- Exportación completa de tareas con filtros
- Formato profesional con headers y estilos

### Multi-tenancy (Preparado)
- Estructura base para multi-inquilino
- Modelo `Tenant` implementado
- Escalabilidad para múltiples organizaciones

### API RESTful
- Respuestas consistentes en JSON
- Códigos de estado HTTP apropiados
- Paginación automática en listados
- Documentación de errores detallada

## 🧪 Testing

```bash
# Ejecutar todas las pruebas
php artisan test

# Pruebas específicas
php artisan test --filter=AuthTest
```

## 📝 Logs y Debugging

```bash
# Logs de aplicación
tail -f storage/logs/laravel.log

# Logs de Apache (en servidor)
sudo tail -f /var/log/apache2/gestor-tareas_error.log
```

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un Pull Request

## 📜 Licencia

Este proyecto está bajo la licencia **MIT**. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Desarrollado por

**Aisaac2205** - *Desarrollador Full Stack*

---

### 🌟 ¿Te gusta el proyecto? ¡Dale una estrella! ⭐

**Backend URL:** `http://your-domain.com/api`  
**Documentación completa:** En desarrollo  
**Status:** ✅ Desplegado y funcional
