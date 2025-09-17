# Laravel API - Gestión de Usuarios y Tareas

API REST en Laravel para gestionar Usuarios y Tareas, con autenticación por tokens (Sanctum), validación robusta y exportación a Excel.

## Características

- ✅ Autenticación por token con Laravel Sanctum
- ✅ CRUD de Usuarios y Tareas
- ✅ Sistema de roles en usuarios: `admin` | `usuario`
- ✅ Validación y respuestas JSON consistentes
- ✅ Relación Eloquent: `Usuario hasMany Tarea` / `Tarea belongsTo Usuario`
- ✅ Exportación de reporte de tareas pendientes a Excel

## Requisitos

- PHP 8.1+
- MySQL/MariaDB
- Composer 2+
- Node.js (solo si compilas assets; para esta API no es requerido)

## Instalación y Configuración

1) Clonar e instalar dependencias
```bash
git clone <TU_REPO_URL>
cd laravel-api
composer install
```

2) Variables de entorno
```bash
cp .env.example .env
php artisan key:generate
```
Edita `.env`:
```env
APP_NAME="Laravel API"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

3) Migraciones (y seed opcional)
```bash
php artisan migrate
# opcional: php artisan db:seed
```

4) Ejecutar servidor
```bash
php artisan serve
# Servirá en http://127.0.0.1:8000
```

## Rutas y Endpoints

### Autenticación
- `POST /api/register` → Registrar usuario (genera token)
- `POST /api/login` → Login (genera token)
- `POST /api/logout` → Logout (revoca tokens) [auth:sanctum]

Ejemplos cURL:
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "password": "123456",
    "rol": "usuario"
  }'

curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@ejemplo.com",
    "password": "123456"
  }'
```

Respuesta típica de login/register:
```json
{
  "message": "Login exitoso",
  "usuario": { "id": 1, "nombre": "Juan Pérez", "email": "juan@ejemplo.com", "rol": "usuario" },
  "token": "1|abcdef..."
}
```

Usar el token en peticiones protegidas:
```
Authorization: Bearer TU_TOKEN
```

### Usuarios
- `GET /api/usuarios/listUsers` → Listar usuarios
- `POST /api/usuarios/addUser` → Crear usuario
- `GET /api/usuarios/getUser/{id}` → Ver usuario
- `PUT /api/usuarios/updateUser/{id}` → Actualizar usuario
- `DELETE /api/usuarios/deleteUser/{id}` → Eliminar usuario

Nota: el modelo `Usuario` oculta `password` por defecto. Puedes ocultar más campos con `$hidden`.

### Tareas
- `GET /api/tareas/` → Listar tareas (incluye usuario relacionado: `id,nombre,email`)
- `POST /api/tareas/` → Crear tarea
- `GET /api/tareas/{id}` → Ver tarea
- `PUT /api/tareas/{id}` → Actualizar tarea
- `DELETE /api/tareas/{id}` → Eliminar tarea
- `GET /api/tareas/usuarios` → Listar usuarios para selector (`id,nombre,email`)
- `GET /api/tareas/report-pendientes` → Descargar Excel con tareas pendientes

Ejemplo crear tarea:
```bash
curl -X POST http://localhost:8000/api/tareas/ \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Preparar informe",
    "descripcion": "Informe mensual",
    "estado": "pendiente",
    "fecha_vencimiento": "2025-12-31",
    "user_id": 1
  }'
```

## Modelos y Relaciones

- `App\Models\Usuario` (Authenticatable)
  - fillable: `nombre, email, password, rol`
  - hidden: `password`
  - relaciones: `tareas()` hasMany

- `App\Models\Tarea`
  - fillable: `titulo, descripcion, estado, fecha_vencimiento, user_id`
  - casts: `fecha_vencimiento: date`
  - relaciones: `user()` belongsTo `Usuario`

## Seguridad

### Mejoras de Seguridad (2025-09)

- **Todas las rutas CRUD protegidas**: Las rutas de usuarios y tareas requieren token válido (`auth:sanctum`). Si el token es inválido o expirado, la API responde con **401 Unauthorized**.
- **Rate limiting en login**: El endpoint `POST /api/login` está limitado a 5 intentos por minuto para evitar ataques de fuerza bruta (`throttle:5,1`).
- **Revocación de tokens en logout**: El endpoint `POST /api/logout` revoca todos los tokens activos del usuario, impidiendo su reutilización.
- **Tokens hasheados**: Los tokens personales se almacenan hasheados en la base de datos (`personal_access_tokens`).
- **Expiración configurable de tokens**: Los tokens expiran automáticamente tras un periodo configurable (por defecto 1 día, ajustable con la variable `SANCTUM_TOKEN_EXPIRATION` en `.env`).
- **CORS restringido**: Solo se permite el origen configurado en la variable `FRONTEND_URL` (por defecto `http://localhost:5173`).
- **Validación robusta de inputs**: Todos los endpoints de login y registro validan exhaustivamente los datos recibidos.
- **Registro de intentos fallidos de login**: Cada intento fallido de login se registra en los logs del sistema, incluyendo email, IP y fecha.
- **Middleware de origen**: Se añadió un middleware personalizado (`validate.origin`) que valida el encabezado `Origin` en rutas protegidas, bloqueando peticiones de orígenes no autorizados.

#### Variables de entorno relevantes

```
FRONTEND_URL=http://localhost:5173         # Origen permitido para CORS y middleware
SANCTUM_TOKEN_EXPIRATION=1440              # Expiración de tokens en minutos (1 día por defecto)
```

#### Ejemplo de respuesta 401 por token inválido/expirado
```json
{
  "message": "Unauthenticated."
}
```

#### Ejemplo de respuesta 403 por origen no permitido
```json
{
  "message": "Origen no permitido."
}
```

#### Notas adicionales
- El rate limiting en login es automático y retorna HTTP 429 si se excede el límite.
- El middleware de origen se aplica a todas las rutas protegidas (`usuarios` y `tareas`).
- Los tokens se revocan en logout usando `$request->user()->tokens()->delete()`.
- Los intentos fallidos de login quedan registrados en los logs de Laravel (`storage/logs/laravel.log`).

## Exportación a Excel

- Endpoint: `GET /api/tareas/report-pendientes`
- Genera un archivo `.xlsx` con tareas en estado pendiente

## Estructura del Proyecto (principal)

```
app/
├─ Http/Controllers/Api/
│  ├─ AuthController.php
│  ├─ UsuarioController.php
│  └─ TareaController.php
├─ Models/
│  ├─ Usuario.php
│  └─ Tarea.php
routes/
└─ api.php
```

Consulta también `ARQUITECTURA.md` y `diagrama-arquitectura.drawio` para un diagrama visual.

## Comandos útiles

```bash
php artisan serve             # Levantar el servidor (puerto 8000)
php artisan migrate           # Ejecutar migraciones
php artisan tinker            # Consola interactiva
php artisan route:list | cat  # Ver rutas
```

## Licencia

MIT.
