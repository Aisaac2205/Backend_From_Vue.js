# 🚀 INSTRUCCIONES DE DESPLIEGUE EN AWS EC2

## 📋 Prerrequisitos en EC2:
- Ubuntu/Amazon Linux con Apache2
- MariaDB/MySQL instalado
- PHP 8.1+ con extensiones: php-mysql, php-curl, php-mbstring, php-xml, php-zip
- Composer instalado

## 🔧 Pasos de instalación:

### 1. **Subir y extraer el proyecto**
```bash
# Subir laravel-api.zip al servidor
scp laravel-api.zip ec2-user@ec2-18-219-51-191.us-east-2.compute.amazonaws.com:~

# Conectar al servidor
ssh ec2-user@ec2-18-219-51-191.us-east-2.compute.amazonaws.com

# Extraer en directorio web
sudo unzip laravel-api.zip -d /var/www/html/
sudo chown -R www-data:www-data /var/www/html/laravel-api
```

### 2. **Configurar el proyecto**
```bash
cd /var/www/html/laravel-api

# Copiar archivo de configuración para producción
cp .env.production .env

# Generar clave de aplicación
php artisan key:generate

# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Configurar permisos
sudo chmod -R 775 storage bootstrap/cache
```

### 3. **Configurar Apache**
```bash
# Copiar configuración de Apache
sudo cp apache-config.conf /etc/apache2/sites-available/laravel-api.conf

# Habilitar el sitio y módulos necesarios
sudo a2ensite laravel-api.conf
sudo a2enmod rewrite headers
sudo a2dissite 000-default

# Reiniciar Apache
sudo systemctl restart apache2
```

### 4. **Configurar la base de datos**
```bash
# Crear la base de datos (si no existe)
mysql -u root -p
CREATE DATABASE IF NOT EXISTS laravel_taller;
exit;

# Ejecutar migraciones
php artisan migrate --force
```

### 5. **Optimizar para producción**
```bash
# Limpiar y cachear configuraciones
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🌍 URLs de prueba:
- Principal: http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/
- Tenant empresa1: http://empresa1.ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/
- Tenant empresa2: http://empresa2.ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/

## 🔍 Verificaciones:
```bash
# Verificar que funcione
curl http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/
curl -X POST http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/register \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Test User","email":"test@example.com","password":"123456","rol":"usuario"}'
```

## ⚠️ **IMPORTANTE - Editar antes de desplegar:**
1. En `.env`: Cambiar `DB_PASSWORD=admin123` por tu contraseña real de MariaDB
2. Verificar que la base de datos `laravel_taller` exista en el servidor
3. Asegurarse de que Apache esté corriendo y configurado