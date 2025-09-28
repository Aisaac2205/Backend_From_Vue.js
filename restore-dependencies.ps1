# 🔄 Script PowerShell para Restaurar dependencias
# Ejecutar después de subir a EC2
# Restaura: node_modules (backend y frontend) + vendor (backend)

Write-Host "🔄 Restaurando carpetas de dependencias..." -ForegroundColor Cyan

# Verificar que estamos en el directorio correcto
if (-not (Test-Path "composer.json")) {
    Write-Host "❌ Error: No estás en la carpeta backend del proyecto" -ForegroundColor Red
    Write-Host "📁 Navega a: C:\Users\Asus\Documents\GestorTareas\backend" -ForegroundColor Yellow
    exit 1
}

# Restaurar node_modules del backend
if (Test-Path "..\node_modules_backend_temp") {
    Write-Host "📦 Restaurando node_modules del backend..." -ForegroundColor Green
    Move-Item "..\node_modules_backend_temp" "node_modules"
    Write-Host "✅ node_modules del backend restaurado" -ForegroundColor Green
} else {
    Write-Host "⚠️  No se encontró node_modules_backend_temp" -ForegroundColor Yellow
}

# Restaurar node_modules del frontend
if (Test-Path "..\node_modules_frontend_temp") {
    Write-Host "📦 Restaurando node_modules del frontend..." -ForegroundColor Green
    Move-Item "..\node_modules_frontend_temp" "..\frontend\node_modules"
    Write-Host "✅ node_modules del frontend restaurado" -ForegroundColor Green
} else {
    Write-Host "⚠️  No se encontró node_modules_frontend_temp" -ForegroundColor Yellow
}

# Restaurar vendor del backend
if (Test-Path "..\vendor_temp") {
    Write-Host "📦 Restaurando vendor del backend..." -ForegroundColor Green
    Move-Item "..\vendor_temp" "vendor"
    Write-Host "✅ vendor del backend restaurado" -ForegroundColor Green
} else {
    Write-Host "⚠️  No se encontró vendor_temp" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🎉 ¡Restauración completada!" -ForegroundColor Cyan
Write-Host ""
Write-Host "📋 Estado actual:" -ForegroundColor White

$backendNodeModules = if (Test-Path "node_modules") { "✅ Presente" } else { "❌ Ausente" }
$frontendNodeModules = if (Test-Path "..\frontend\node_modules") { "✅ Presente" } else { "❌ Ausente" }
$backendVendor = if (Test-Path "vendor") { "✅ Presente" } else { "❌ Ausente" }

Write-Host "   Backend node_modules: $backendNodeModules" -ForegroundColor White
Write-Host "   Frontend node_modules: $frontendNodeModules" -ForegroundColor White
Write-Host "   Backend vendor: $backendVendor" -ForegroundColor White
Write-Host ""
Write-Host "💡 Ahora puedes seguir desarrollando localmente con normalidad" -ForegroundColor Cyan