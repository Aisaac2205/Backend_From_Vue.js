#!/bin/bash

# Script de despliegue para Laravel API en AWS EC2
# chmod +x deploy.sh

echo "🚀 Iniciando despliegue Laravel API en AWS EC2..."

# 1. Actualizar repositorio (si usas Git)
echo "📥 Actualizando código..."
git pull origin main

# 2. Instalar/actualizar dependencias
echo "📦 Instalando dependencias..."
composer install --no-dev --optimize-autoloader

# 3. Configurar archivo .env
echo "⚙️ Configurando .env..."
if [ ! -f .env ]; then
    cp .env.production .env
    echo "⚠️ IMPORTANTE: Edita el archivo .env con las credenciales correctas"
    echo "⚠️ Luego ejecuta: php artisan key:generate"
fi

# 4. Limpiar y optimizar caché
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 5. Optimizar para producción
echo "⚡ Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Ejecutar migraciones
echo "🗃️ Ejecutando migraciones..."
php artisan migrate --force

# 7. Configurar permisos
echo "🔐 Configurando permisos..."
sudo chown -R www-data:www-data /var/www/html/laravel-api
sudo chmod -R 755 /var/www/html/laravel-api
sudo chmod -R 775 /var/www/html/laravel-api/storage
sudo chmod -R 775 /var/www/html/laravel-api/bootstrap/cache

# 8. Configurar Apache (solo la primera vez)
if [ ! -f /etc/apache2/sites-available/laravel-api.conf ]; then
    echo "🌐 Configurando Apache..."
    sudo cp apache-config.conf /etc/apache2/sites-available/laravel-api.conf
    sudo a2ensite laravel-api.conf
    sudo a2enmod rewrite headers
    sudo systemctl reload apache2
fi

# 9. Verificar estado
echo "✅ Verificando instalación..."
php artisan --version
php artisan route:list | head -10

echo "🎉 Despliegue completado!"
echo "🌍 URL: http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com"
echo "📋 Tenants de ejemplo:"
echo "   - http://empresa1.ec2-18-219-51-191.us-east-2.compute.amazonaws.com"
echo "   - http://empresa2.ec2-18-219-51-191.us-east-2.compute.amazonaws.com"
echo ""
echo "⚠️ RECORDATORIOS:"
echo "   1. Editar .env con credenciales reales"
echo "   2. Ejecutar: php artisan key:generate"
echo "   3. Verificar conexión a la base de datos"
echo "   4. Probar endpoints de API"