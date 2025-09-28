#!/bin/bash

# 🚀 Script de Configuración Post-SFTP para EC2
# Ejecutar después de arrastrar la carpeta backend a /var/www/

echo "🚀 Iniciando configuración del Gestor de Tareas en EC2..."

# 1. Configurar permisos temporales para instalación
echo "🔐 Configurando permisos temporales..."
sudo chown -R ubuntu:ubuntu /var/www/backend
sudo chmod -R 755 /var/www/backend

# 2. Navegar al directorio del proyecto
cd /var/www/backend

# 3. Instalar dependencias de Composer
echo "📦 Instalando dependencias de Composer..."
composer install --optimize-autoloader --no-dev

# 3.5. Configurar permisos finales para Apache
echo "🔐 Configurando permisos finales para Apache..."
sudo chown -R www-data:www-data /var/www/backend
sudo chmod -R 775 /var/www/backend/storage
sudo chmod -R 775 /var/www/backend/bootstrap/cache
sudo chmod +x /var/www/backend/artisan

# 3.1 Instalar dependencias de Node.js (para assets)
echo "📦 Instalando dependencias de Node.js..."
npm install --production

# 4. Verificar APP_KEY (mantener la existente)
echo "🔑 Verificando clave de aplicación..."
echo "✅ APP_KEY ya configurada en .env"

# 5. Limpiar cache
echo "🧹 Limpiando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Ejecutar migraciones
echo "🗃️ Ejecutando migraciones de base de datos..."
php artisan migrate --force

# 7. Poblar base de datos
echo "🌱 Poblando base de datos con datos de prueba..."
php artisan db:seed --force

# 8. Configurar Apache Virtual Host
echo "⚙️ Configurando Apache..."
sudo tee /etc/apache2/sites-available/gestor-tareas.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName ec2-18-219-51-191.us-east-2.compute.amazonaws.com
    DocumentRoot /var/www/backend/public
    
    <Directory /var/www/backend/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
        
        # Configuración para SPA Vue.js
        FallbackResource /index.php
    </Directory>
    
    # Logs
    ErrorLog \${APACHE_LOG_DIR}/gestor-tareas_error.log
    CustomLog \${APACHE_LOG_DIR}/gestor-tareas_access.log combined
    
    # Seguridad - Ocultar archivos sensibles
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
    
    <FilesMatch "\.(env|log)$">
        Require all denied
    </FilesMatch>
</VirtualHost>
EOF

# 9. Habilitar módulos necesarios de Apache
echo "🔧 Habilitando módulos de Apache..."
sudo a2enmod rewrite
sudo a2enmod php8.1

# 10. Habilitar sitio en Apache
echo "🔗 Habilitando sitio en Apache..."
sudo a2ensite gestor-tareas.conf
sudo a2dissite 000-default.conf

# 11. Verificar configuración de Apache
echo "✅ Verificando configuración de Apache..."
sudo apache2ctl configtest

# 12. Reiniciar servicios
echo "🔄 Reiniciando servicios..."
sudo systemctl restart apache2

# 13. Verificar estado de servicios
echo "📊 Verificando estado de servicios..."
sudo systemctl status apache2 --no-pager -l

# 13. Mostrar información final
echo ""
echo "🎉 ¡Configuración completada!"
echo ""
echo "🌐 URLs disponibles:"
echo "   Frontend: http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com"
echo "   API: http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api"
echo ""
echo "📋 Verificar funcionamiento:"
echo "   curl -I http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com"
echo "   curl http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api"
echo ""
echo "📝 Logs importantes:"
echo "   Apache: sudo tail -f /var/log/apache2/gestor-tareas_error.log"
echo "   Laravel: sudo tail -f /var/www/backend/storage/logs/laravel.log"
echo ""
echo "✅ ¡Tu aplicación está lista para usar!"