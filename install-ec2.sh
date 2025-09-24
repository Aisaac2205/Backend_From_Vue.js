#!/bin/bash

# Script de instalación y configuración en AWS EC2
# Ejecutar como root: sudo bash install-ec2.sh

echo "🚀 Instalando Laravel Multi-Tenant en AWS EC2..."

# Variables
PROJECT_DIR="/var/www/laravel-api"
WEB_USER="www-data"

echo ""
echo "📦 Instalando dependencias del sistema..."

# Actualizar sistema
apt update && apt upgrade -y

# Instalar PHP 8.1+, Apache, MariaDB
apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-intl
apt install -y apache2 mariadb-server
apt install -y composer git unzip

# Habilitar módulos de Apache
a2enmod rewrite
a2enmod ssl
a2enmod headers

echo ""
echo "🔧 Configurando MariaDB..."

# Configurar MariaDB
systemctl start mariadb
systemctl enable mariadb

# Configurar usuario root de MariaDB (contraseña: admin123)
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'admin123';"
mysql -u root -padmin123 -e "CREATE DATABASE IF NOT EXISTS laravel_taller CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -padmin123 -e "FLUSH PRIVILEGES;"

echo ""
echo "📁 Configurando aplicación..."

# Crear directorio del proyecto
mkdir -p $PROJECT_DIR
cd $PROJECT_DIR

# Copiar archivos (asumiendo que ya están en /tmp/laravel-api/)
if [ -d "/tmp/laravel-api" ]; then
    cp -r /tmp/laravel-api/* $PROJECT_DIR/
    cp -r /tmp/laravel-api/.* $PROJECT_DIR/ 2>/dev/null || true
else
    echo "❌ Directorio /tmp/laravel-api no encontrado. Asegúrate de subir los archivos primero."
    exit 1
fi

# Configurar permisos
chown -R $WEB_USER:$WEB_USER $PROJECT_DIR
chmod -R 755 $PROJECT_DIR
chmod -R 775 $PROJECT_DIR/storage $PROJECT_DIR/bootstrap/cache

echo ""
echo "🔑 Configurando variables de entorno..."

# Copiar archivo de producción
cp .env.production.ec2 .env

# Generar APP_KEY
php artisan key:generate --force

# Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo "📦 Instalando dependencias PHP..."

# Instalar dependencias de Composer
composer install --no-dev --optimize-autoloader

echo ""
echo "🗄️ Configurando base de datos..."

# Ejecutar migraciones
php artisan migrate --force
php artisan migrate --path=database/migrations/2025_09_24_182508_create_tenants_table.php --force

echo ""
echo "🌐 Configurando Apache..."

# Crear configuración de Apache
cat > /etc/apache2/sites-available/laravel-multitenancy.conf << 'EOF'
<VirtualHost *:80>
    ServerName ec2-18-219-51-191.us-east-2.compute.amazonaws.com
    ServerAlias *.ec2-18-219-51-191.us-east-2.compute.amazonaws.com
    DocumentRoot /var/www/laravel-api/public
    
    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    
    <Directory /var/www/laravel-api/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/laravel_error.log
    CustomLog ${APACHE_LOG_DIR}/laravel_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName ec2-18-219-51-191.us-east-2.compute.amazonaws.com
    ServerAlias *.ec2-18-219-51-191.us-east-2.compute.amazonaws.com
    DocumentRoot /var/www/laravel-api/public
    
    # SSL Configuration (si tienes certificados)
    # SSLEngine on
    # SSLCertificateFile /path/to/certificate.crt
    # SSLCertificateKeyFile /path/to/private.key
    
    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    
    <Directory /var/www/laravel-api/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/laravel_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/laravel_ssl_access.log combined
</VirtualHost>
EOF

# Habilitar el sitio
a2dissite 000-default
a2ensite laravel-multitenancy
systemctl reload apache2

echo ""
echo "👤 Creando tenant de ejemplo..."

# Crear tenant demo
cd $PROJECT_DIR
php artisan tenant:create demo "Empresa Demo" demo

echo ""
echo "🔐 Información de usuarios por defecto:"
echo "   📧 Admin: admin@admin.com"
echo "   🔑 Password: admin123"
echo "   👤 Usuario: usuario@demo.com / demo123"
echo "   ✏️ Editor: editor@demo.com / editor123"

echo ""
echo "🎉 ¡Instalación completada!"
echo ""
echo "🌐 URLs disponibles:"
echo "   Aplicación principal: http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com"
echo "   API de gestión: http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/admin/tenants"
echo "   Tenant demo: http://demo.ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/usuarios/listUsers"
echo ""
echo "🔧 Comandos útiles:"
echo "   Crear tenant: php artisan tenant:create [id] [nombre] [subdominio]"
echo "   Ver tenants: php artisan tinker --execute='App\\Models\\Tenant::all()'"
echo "   Logs: tail -f /var/log/apache2/laravel_error.log"
echo ""
echo "📚 Lee MULTITENANCY.md para más información"