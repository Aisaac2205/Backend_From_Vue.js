# 🚀 Guía Rápida: SFTP + Apache en EC2

## ✅ **Pasos Simplificados para Apache**

Ya que tienes **Apache y PHP instalados**, estos son los pasos exactos después de arrastrar la carpeta `backend` con Termius SFTP:

---

## 📁 **1. Después del SFTP (Termius):**

### **✅ Ya hiciste:**
- Arrastrar carpeta `backend` → `/var/www/`
- Resultado: `/var/www/backend/` ✅

---

## 🔧 **2. Comandos en Terminal EC2:**

### **🔐 Permisos (Copiar y pegar todo junto):**
```bash
sudo chown -R www-data:www-data /var/www/backend
sudo chmod -R 775 /var/www/backend/storage
sudo chmod -R 775 /var/www/backend/bootstrap/cache
sudo chmod +x /var/www/backend/artisan
```

### **📦 Dependencias:**
```bash
cd /var/www/backend
composer install --optimize-autoloader --no-dev
php artisan key:generate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### **🗃️ Base de Datos:**
```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## ⚙️ **3. Configurar Apache Virtual Host:**

### **📝 Crear archivo de configuración:**
```bash
sudo nano /etc/apache2/sites-available/gestor-tareas.conf
```

### **📋 Copiar este contenido exacto:**
```apache
<VirtualHost *:80>
    ServerName ec2-18-219-51-191.us-east-2.compute.amazonaws.com
    DocumentRoot /var/www/backend/public
    
    <Directory /var/www/backend/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
        FallbackResource /index.php
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/gestor-tareas_error.log
    CustomLog ${APACHE_LOG_DIR}/gestor-tareas_access.log combined
    
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
    
    <FilesMatch "\.(env|log)$">
        Require all denied
    </FilesMatch>
</VirtualHost>
```

### **🔗 Habilitar sitio:**
```bash
sudo a2enmod rewrite
sudo a2enmod php8.1
sudo a2ensite gestor-tareas.conf
sudo a2dissite 000-default.conf
sudo apache2ctl configtest
sudo systemctl restart apache2
```

---

## ✅ **4. Verificar Funcionamiento:**

### **🌐 Probar URLs:**
```bash
# Probar que carga el sitio
curl -I http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com

# Probar API
curl http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api
```

### **📝 Ver logs si hay errores:**
```bash
# Ver errores de Apache
sudo tail -f /var/log/apache2/gestor-tareas_error.log

# Ver errores de Laravel
sudo tail -f /var/www/backend/storage/logs/laravel.log
```

---

## 🎯 **Resultado Esperado:**

Después de estos pasos, tu aplicación debería estar funcionando en:
- **🌐 Frontend:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com
- **🔌 API:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api

---

## 🚨 **Problemas Comunes y Soluciones:**

### **❌ Error 403 Forbidden:**
```bash
sudo chown -R www-data:www-data /var/www/backend
sudo chmod -R 755 /var/www/backend
sudo chmod -R 775 /var/www/backend/storage
```

### **❌ Error 500 Internal Server:**
```bash
sudo tail -f /var/log/apache2/gestor-tareas_error.log
```

### **❌ Error de Base de Datos:**
```bash
# Verificar conexión
php artisan tinker
>>> DB::connection()->getPdo();
```

### **❌ Composer no instalado:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 📱 **Script Automático (Opcional):**

Si prefieres un script que haga todo automáticamente, puedes usar el archivo `setup-ec2.sh` que está en tu proyecto:

```bash
cd /var/www/backend
chmod +x setup-ec2.sh
sudo ./setup-ec2.sh
```

---

**🎯 ¡Con estos pasos tu aplicación debería funcionar perfectamente!** 🚀

**📅 Guía actualizada:** 28 de septiembre de 2025  
**⚙️ Servidor:** Apache en Ubuntu/EC2  
**🗃️ Base de datos:** MariaDB (laravel_taller)