# 🚀 Guía de Despliegue con Termius SFTP a EC2

## ✅ **Respuesta Directa: ¡SÍ FUNCIONARÁ!**

Arrastrar la carpeta `backend` a `/var/www/` usando Termius SFTP es una **excelente estrategia** y funcionará perfectamente. Te explico todo el proceso:

---

## 📁 **Proceso de Despliegue con SFTP**

### 1️⃣ **Preparación Local (Antes de arrastrar):**

#### **✅ Base de Datos Actualizada:**
```bash
# backend/.env (YA ACTUALIZADO)
DB_DATABASE=laravel_taller  # ✅ Coincide con tu DB en EC2
DB_USERNAME=root
DB_PASSWORD=admin123
```

#### **✅ Configuración EC2 Lista:**
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com
```

---

### 2️⃣ **Pasos con Termius SFTP:**

#### **📂 Arrastrar Archivos:**
1. **Conectar a EC2** via Termius SFTP
2. **Arrastrar carpeta `backend`** a `/var/www/`
3. **Resultado:** `/var/www/backend/` con todo tu proyecto

#### **📋 Estructura Resultante:**
```
/var/www/backend/
├── app/                 # ✅ Controllers, Models, etc.
├── config/              # ✅ Configuraciones Laravel
├── database/            # ✅ Migraciones y seeders
├── public/              # ✅ Frontend compilado incluido
├── routes/              # ✅ Rutas API
├── storage/             # ✅ Logs y cache
├── vendor/              # ⚠️ Requiere composer install
├── .env                 # ✅ Configurado para EC2
├── artisan              # ✅ CLI Laravel
└── composer.json        # ✅ Dependencias
```

---

## ⚙️ **Configuración Post-SFTP en EC2**

### 3️⃣ **Comandos en Terminal EC2:**

#### **🔐 Permisos Correctos:**
```bash
# Cambiar propietario
sudo chown -R www-data:www-data /var/www/backend

# Permisos de escritura
sudo chmod -R 775 /var/www/backend/storage
sudo chmod -R 775 /var/www/backend/bootstrap/cache

# Permisos de ejecución para artisan
sudo chmod +x /var/www/backend/artisan
```

#### **📦 Instalar Dependencias:**
```bash
cd /var/www/backend

# Instalar dependencias PHP (sin dev para producción)
composer install --optimize-autoloader --no-dev

# Generar APP_KEY si no está generada
php artisan key:generate

# Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

#### **🗃️ Base de Datos:**
```bash
# Ejecutar migraciones
php artisan migrate

# Poblar base de datos con datos de prueba
php artisan db:seed
```

---

### 4️⃣ **Configuración Apache:**

#### **📝 Archivo de Configuración:**
```bash
sudo nano /etc/apache2/sites-available/gestor-tareas.conf
```

#### **⚙️ Contenido del Archivo:**
```apache
<VirtualHost *:80>
    ServerName ec2-18-219-51-191.us-east-2.compute.amazonaws.com
    
    # ✅ IMPORTANTE: Apuntar a /public dentro de backend
    DocumentRoot /var/www/backend/public
    
    <Directory /var/www/backend/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
        
        # Configuración para SPA Vue.js
        FallbackResource /index.php
    </Directory>
    
    # Logs específicos del proyecto
    ErrorLog ${APACHE_LOG_DIR}/gestor-tareas_error.log
    CustomLog ${APACHE_LOG_DIR}/gestor-tareas_access.log combined
    
    # Seguridad - Ocultar archivos sensibles
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
    
    <FilesMatch "\.(env|log)$">
        Require all denied
    </FilesMatch>
</VirtualHost>
```

#### **🔗 Habilitar Sitio:**
```bash
# Habilitar módulos necesarios
sudo a2enmod rewrite
sudo a2enmod php8.1

# Habilitar sitio
sudo a2ensite gestor-tareas.conf

# Deshabilitar sitio por defecto
sudo a2dissite 000-default.conf

# Verificar configuración
sudo apache2ctl configtest

# Reiniciar Apache
sudo systemctl restart apache2
```

---

## 🎯 **Ventajas del Método SFTP**

### ✅ **Pros:**
- **🚀 Súper rápido** - Solo arrastrar y soltar
- **📁 Completo** - Todos los archivos de una vez
- **🎨 Visual** - Interfaz gráfica fácil de usar
- **🔄 Sincronización** - Fácil actualizar archivos individuales
- **💻 Sin comandos Git** en servidor (más limpio)

### ⚠️ **Aspectos a Considerar:**
- **📦 Dependencias** - Requiere `composer install` en servidor
- **🔐 Permisos** - Necesitas configurar permisos después
- **⚙️ Configuración** - Nginx/Apache debe apuntar correctamente

---

## 🏁 **Resultado Final Esperado**

### **🌐 URLs Funcionales:**
- **Frontend:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com
- **API:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api
- **Login:** http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/login

### **✨ Funcionalidades:**
- ✅ Autenticación de usuarios
- ✅ CRUD de tareas y usuarios
- ✅ Funciones administrativas
- ✅ Descarga de reportes Excel
- ✅ SPA Vue.js completamente funcional

---

## 📋 **Checklist Post-Despliegue:**

```bash
# ✅ Verificar que el sitio carga
curl -I http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com

# ✅ Verificar API
curl http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com/api/usuarios

# ✅ Verificar logs
sudo tail -f /var/log/apache2/gestor-tareas_error.log
sudo tail -f /var/www/backend/storage/logs/laravel.log
```

---

## 🎓 **Conclusión**

**¡Absolutamente funcionará!** El método SFTP con Termius es:
- ✅ **Práctico y eficiente**
- ✅ **Perfecto para este proyecto**
- ✅ **Más rápido que clonar con Git**
- ✅ **Ideal para actualizaciones futuras**

Solo asegúrate de seguir los pasos post-SFTP para permisos, dependencias y configuración de Nginx.

---

**📅 Guía creada:** 28 de septiembre de 2025  
**🎯 Método:** SFTP con Termius a `/var/www/backend`  
**✅ Estado:** Completamente viable y recomendado