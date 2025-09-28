#!/bin/bash

# 🔄 Script para Restaurar dependencias después del despliegue
# Ejecutar en tu máquina local después de subir a EC2
# Restaura: node_modules (backend y frontend) + vendor (backend)

echo "🔄 Restaurando carpetas de dependencias..."

# Verificar que estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    echo "❌ Error: No estás en la carpeta backend del proyecto"
    echo "📁 Navega a: C:\Users\Asus\Documents\GestorTareas\backend"
    exit 1
fi

# Restaurar node_modules del backend
if [ -d "../node_modules_backend_temp" ]; then
    echo "📦 Restaurando node_modules del backend..."
    mv "../node_modules_backend_temp" "node_modules"
    echo "✅ node_modules del backend restaurado"
else
    echo "⚠️  No se encontró node_modules_backend_temp"
fi

# Restaurar node_modules del frontend
if [ -d "../node_modules_frontend_temp" ]; then
    echo "📦 Restaurando node_modules del frontend..."
    mv "../node_modules_frontend_temp" "../frontend/node_modules"
    echo "✅ node_modules del frontend restaurado"
else
    echo "⚠️  No se encontró node_modules_frontend_temp"
fi

# Restaurar vendor del backend
if [ -d "../vendor_temp" ]; then
    echo "📦 Restaurando vendor del backend..."
    mv "../vendor_temp" "vendor"
    echo "✅ vendor del backend restaurado"
else
    echo "⚠️  No se encontró vendor_temp"
fi

echo ""
echo "🎉 ¡Restauración completada!"
echo ""
echo "📋 Estado actual:"
echo "   Backend node_modules: $([ -d "node_modules" ] && echo "✅ Presente" || echo "❌ Ausente")"
echo "   Frontend node_modules: $([ -d "../frontend/node_modules" ] && echo "✅ Presente" || echo "❌ Ausente")"
echo "   Backend vendor: $([ -d "vendor" ] && echo "✅ Presente" || echo "❌ Ausente")"
echo ""
echo "💡 Ahora puedes seguir desarrollando localmente con normalidad"