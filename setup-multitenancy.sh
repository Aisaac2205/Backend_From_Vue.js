#!/bin/bash

# Script de configuración inicial para sistema multi-tenant
# Ejecutar después de configurar credenciales MySQL correctas

echo "🚀 Configurando sistema multi-tenant con bases de datos separadas..."

echo ""
echo "📋 Verificando configuración..."

# Verificar conexión a BD
echo "🔍 Verificando conexión a base de datos..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Conexión exitosa\n';" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Conexión a BD exitosa"
else
    echo "❌ Error de conexión a BD. Verificar credenciales en .env"
    exit 1
fi

echo ""
echo "📦 Ejecutando migración de tabla tenants..."

# Limpiar cache
php artisan config:clear
php artisan cache:clear

# Ejecutar migración de tenants
php artisan migrate --path=database/migrations/2025_09_24_182508_create_tenants_table.php --force

if [ $? -eq 0 ]; then
    echo "✅ Tabla tenants creada exitosamente"
else
    echo "❌ Error creando tabla tenants"
    exit 1
fi

echo ""
echo "👤 Creando tenant de ejemplo..."

# Crear tenant de prueba
php artisan tenant:create demo "Empresa Demo" demo --domain=demo.com

if [ $? -eq 0 ]; then
    echo "✅ Tenant demo creado exitosamente"
    echo ""
    echo "🌐 URLs de acceso:"
    echo "   Gestión: https://$(grep BASE_DOMAIN .env | cut -d'=' -f2)/api/admin/tenants"
    echo "   Tenant Demo: https://demo.$(grep BASE_DOMAIN .env | cut -d'=' -f2)/api/usuarios/listUsers"
else
    echo "❌ Error creando tenant demo"
fi

echo ""
echo "🎉 Configuración completada!"
echo ""
echo "📚 Leer MULTITENANCY.md para más información"
echo "🔧 Usar 'php artisan tenant:create' para crear más tenants"