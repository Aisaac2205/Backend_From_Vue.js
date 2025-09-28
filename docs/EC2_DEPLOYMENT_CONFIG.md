# 🚀 Configuración para Despliegue en EC2

## 🌐 **Servidor AWS EC2**
**URL:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com

---

## ⚙️ **Configuraciones Actualizadas**

### 📱 **Frontend (Vue.js):**
- ✅ `.env` actualizado con URL de EC2
- ✅ `api.js` configurado para usar variable de entorno
- ✅ Compilación realizada con nuevas configuraciones
- ✅ Assets optimizados para producción

### 🖥️ **Backend (Laravel):**
- ✅ `.env` configurado para producción en EC2
- ✅ `config/cors.php` actualizado con dominios permitidos
- ✅ `config/sanctum.php` configurado para EC2
- ✅ Frontend compilado integrado en `/public`

---

## 🔧 **Cambios Realizados:**

### 1. **Frontend Configuration:**
```bash
# frontend/.env
VITE_API_URL=http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api
```

### 2. **Backend Configuration:**
```bash
# backend/.env
APP_NAME="Gestor de Tareas"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com
FRONTEND_URL=http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com
SANCTUM_STATEFUL_DOMAINS=ec2-18-219-51-191.us-east-2.compute.amazonaws.com
SESSION_DOMAIN=ec2-18-219-51-191.us-east-2.compute.amazonaws.com
```

### 3. **CORS Configuration:**
```php
// config/cors.php
'allowed_origins' => [
    'http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com',
    'https://ec2-18-219-51-191.us-east-2.compute.amazonaws.com',
    // + desarrollo local mantenido
],
```

### 4. **Sanctum Configuration:**
```php
// config/sanctum.php
'stateful' => [
    'ec2-18-219-51-191.us-east-2.compute.amazonaws.com',
    // + dominios locales mantenidos
],
```

---

## 📋 **Pasos para Despliegue en EC2:**

### 1. **Preparación del Servidor:**
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependencias
sudo apt install nginx mysql-server php8.1 php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. **Configuración del Proyecto:**
```bash
# Clonar repositorio
git clone https://github.com/Aisaac2205/GestorDetareas.git
cd GestorDetareas

# Instalar dependencias
composer install --optimize-autoloader --no-dev

# Configurar permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Configurar base de datos
php artisan migrate
php artisan db:seed
```

### 3. **Configuración de Nginx:**
```nginx
server {
    listen 80;
    server_name ec2-18-219-51-191.us-east-2.compute.amazonaws.com;
    root /var/www/GestorDetareas/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔐 **Configuración de Base de Datos:**

### **Crear Base de Datos:**
```sql
CREATE DATABASE gestor_tareas;
CREATE USER 'gestor_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON gestor_tareas.* TO 'gestor_user'@'localhost';
FLUSH PRIVILEGES;
```

### **Actualizar .env:**
```bash
DB_DATABASE=gestor_tareas
DB_USERNAME=gestor_user
DB_PASSWORD=secure_password
```

---

## ✅ **Verificación de Funcionamiento:**

### **Endpoints a Probar:**
- 🌐 **Frontend:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com
- 🔌 **API:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api
- 🔐 **Login:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/login

### **Funcionalidades:**
- ✅ Registro y login de usuarios
- ✅ CRUD de tareas con asignaciones
- ✅ Funciones administrativas
- ✅ Descarga de reportes Excel
- ✅ Interfaz Vue.js completamente funcional

---

**📅 Configuración actualizada:** 28 de septiembre de 2025  
**🎯 Estado:** Listo para despliegue en EC2  
**🔗 URL objetivo:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com