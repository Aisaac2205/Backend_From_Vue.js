# 🚀 Optimización de Despliegue - Dependencias Excluidas

## ✅ **Carpetas Movidas Temporalmente**

Para acelerar la subida SFTP, se han movido las siguientes carpetas pesadas:

### 📦 **Dependencias Excluidas:**
- ✅ **`backend/node_modules/`** → `node_modules_backend_temp/`
- ✅ **`frontend/node_modules/`** → `node_modules_frontend_temp/`
- ✅ **`backend/vendor/`** → `vendor_temp/` (**56.56 MB ahorrados**)

---

## 🎯 **Beneficios de la Optimización:**

### ⚡ **Velocidad de Subida:**
- **Sin optimización:** ~70+ MB de dependencias
- **Con optimización:** Solo código fuente (~5-10 MB)
- **Mejora:** 85-90% más rápido la subida SFTP

### 📊 **Tamaños Excluidos:**
- `vendor/`: **56.56 MB** (dependencias PHP)
- `node_modules/`: **~15-20 MB** (dependencias Node.js)
- **Total ahorrado:** ~70+ MB

---

## 🔧 **Proceso de Despliegue Optimizado:**

### 1️⃣ **SFTP con Termius:**
```
✅ Arrastrar carpeta backend/ → /var/www/backend/
   (Solo código fuente, sin dependencias pesadas)
```

### 2️⃣ **Script setup-ec2.sh en EC2:**
```bash
cd /var/www/backend
chmod +x setup-ec2.sh
sudo ./setup-ec2.sh
```

**El script instalará automáticamente:**
- ✅ `composer install` → Regenera `vendor/`
- ✅ `npm install --production` → Regenera `node_modules/`

### 3️⃣ **Restaurar en Local (después del despliegue):**
```powershell
# En Windows PowerShell:
.\restore-dependencies.ps1

# En Linux/Mac:
./restore-dependencies.sh
```

---

## 📋 **Scripts Creados:**

### 🔄 **Restauración de Dependencias:**
- **`restore-dependencies.ps1`** - Script PowerShell para Windows
- **`restore-dependencies.sh`** - Script Bash para Linux/Mac

### ⚙️ **Configuración EC2:**
- **`setup-ec2.sh`** - Actualizado para instalar todas las dependencias

---

## 🎯 **Estado Actual del Proyecto:**

### ✅ **Listo para SFTP:**
```
backend/
├── app/                    # ✅ Código fuente
├── config/                 # ✅ Configuraciones
├── database/               # ✅ Migraciones
├── public/                 # ✅ Frontend compilado
├── routes/                 # ✅ Rutas API
├── storage/                # ✅ Logs y cache
├── .env                    # ✅ Configurado para EC2
├── composer.json           # ✅ Lista de dependencias PHP
├── package.json            # ✅ Lista de dependencias Node.js
├── setup-ec2.sh            # ✅ Script de configuración
├── restore-dependencies.*  # ✅ Scripts de restauración
└── [SIN vendor/ ni node_modules/] # ⚡ Optimizado
```

### 📁 **Carpetas Temporales:**
```
../
├── node_modules_backend_temp/    # 🔄 Para restaurar después
├── node_modules_frontend_temp/   # 🔄 Para restaurar después
└── vendor_temp/                  # 🔄 Para restaurar después
```

---

## 🎉 **Resultado Final:**

### 🚀 **Para Despliegue:**
1. **SFTP súper rápido** (solo código fuente)
2. **Script automático** instala dependencias en EC2
3. **Aplicación funcionando** en minutos

### 💻 **Para Desarrollo Local:**
1. **Ejecutar script de restauración** después del despliegue
2. **Continuar desarrollando** con normalidad
3. **Todas las dependencias** restauradas automáticamente

---

**📅 Optimización completada:** 28 de septiembre de 2025  
**⚡ Mejora de velocidad:** 85-90% más rápido  
**💾 Espacio ahorrado:** ~70+ MB en transferencia SFTP