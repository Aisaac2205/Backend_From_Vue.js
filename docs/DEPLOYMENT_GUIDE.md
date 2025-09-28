# Gestor de Tareas - Laravel + Vue.js

## 🛠️ Soluciones Implementadas

### Problemas Corregidos:

1. **❌ Logout caía el servidor**
   - ✅ Corregido: Mejorado el método logout para solo revocar el token actual
   - ✅ Añadido manejo de errores con try-catch

2. **❌ No se podían crear usuarios desde el frontend**
   - ✅ Corregido: Endpoints actualizados de `/usuarios/addUser` a `/usuarios`
   - ✅ Añadida validación de permisos (solo admins pueden crear usuarios)
   - ✅ Mejorado manejo de errores en el frontend

3. **❌ Como admin no mostraba usuarios de la base de datos**
   - ✅ Corregido: Endpoint actualizado de `/usuarios/listUsers` a `/usuarios`
   - ✅ Añadida autenticación con Sanctum a todas las rutas de usuarios
   - ✅ Mejorado manejo de respuestas en el frontend

4. **❌ Problemas de autenticación**
   - ✅ Añadido guard de Sanctum en `config/auth.php`
   - ✅ Protegidas todas las rutas API con middleware `auth:sanctum`
   - ✅ Interceptor de axios mejorado para manejar tokens expirados

## 🚀 Deployment en AWS

### 1. Preparación del Proyecto

Ejecuta uno de estos scripts para compilar el frontend y copiarlo al backend:

**Windows:**
```bash
build.bat
```

**Linux/Mac:**
```bash
chmod +x build.sh
./build.sh
```

### 2. Configuración del Backend

1. Copia `.env.production` a `.env` y configura:
   ```bash
   # Base de datos
   DB_HOST=tu-host-de-base-de-datos
   DB_DATABASE=tu-base-de-datos
   DB_USERNAME=tu-usuario
   DB_PASSWORD=tu-contraseña
   
   # URLs para producción
   APP_URL=https://tu-dominio-aws.com
   FRONTEND_URL=https://tu-dominio-aws.com
   SANCTUM_STATEFUL_DOMAINS=tu-dominio-aws.com
   SESSION_DOMAIN=.tu-dominio-aws.com
   ```

2. Generar clave de aplicación:
   ```bash
   php artisan key:generate
   ```

3. Ejecutar migraciones:
   ```bash
   php artisan migrate
   ```

4. Optimizar para producción:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### 3. Estructura Final

Después del build, el proyecto tendrá esta estructura:

```
backend/
├── public/
│   ├── index.html          # Frontend compilado
│   ├── assets/            # CSS y JS compilados
│   └── api/               # Endpoints de la API
├── app/
├── config/
└── ...resto del backend Laravel
```

### 4. Configuración del Servidor Web

**Apache (.htaccess ya incluido)**
- El proyecto funcionará automáticamente

**Nginx:**
```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/html/backend/public;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    
    index index.php index.html;
    
    charset utf-8;
    
    # Servir frontend para rutas SPA
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # API endpoints
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🧪 Testing Local

1. **Backend:**
   ```bash
   cd backend
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Frontend (desarrollo):**
   ```bash
   cd frontend
   npm run dev
   ```

3. **Frontend compilado con backend:**
   ```bash
   # Ejecutar build.bat o build.sh
   cd backend
   php artisan serve
   # Visitar http://localhost:8000
   ```

## 📝 Notas Importantes

- **Seguridad:** Todas las rutas API están protegidas con autenticación Sanctum
- **CORS:** Configurado para dominios específicos en producción
- **Roles:** Solo usuarios admin pueden crear otros usuarios
- **Tokens:** Se manejan automáticamente con localStorage e interceptores
- **SPA:** El backend sirve el frontend como una Single Page Application

## 🔧 Troubleshooting

**Problema:** Error 401 al hacer peticiones
- **Solución:** Verificar que el token esté en localStorage y sea válido

**Problema:** CORS errors
- **Solución:** Añadir el dominio en `backend/config/cors.php`

**Problema:** Rutas 404 después del build
- **Solución:** Verificar configuración del servidor web para SPA

**Problema:** Base de datos no conecta
- **Solución:** Verificar credenciales en `.env` y que el servidor MySQL esté ejecutándose