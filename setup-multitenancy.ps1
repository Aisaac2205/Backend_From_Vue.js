# Script de configuración inicial para sistema multi-tenant (Windows)
# Ejecutar después de configurar credenciales MySQL correctas

Write-Host "🚀 Configurando sistema multi-tenant con bases de datos separadas..." -ForegroundColor Green

Write-Host ""
Write-Host "📋 Verificando configuración..." -ForegroundColor Yellow

# Verificar conexión a BD
Write-Host "🔍 Verificando conexión a base de datos..." -ForegroundColor Cyan

try {
    $result = php artisan tinker --execute="DB::connection()->getPdo(); echo 'Conexión exitosa';" 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Conexión a BD exitosa" -ForegroundColor Green
    } else {
        Write-Host "❌ Error de conexión a BD. Verificar credenciales en .env" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "❌ Error de conexión a BD. Verificar credenciales en .env" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "📦 Ejecutando migración de tabla tenants..." -ForegroundColor Yellow

# Limpiar cache
php artisan config:clear
php artisan cache:clear

# Ejecutar migración de tenants
Write-Host "Ejecutando migración..." -ForegroundColor Cyan
php artisan migrate --path=database/migrations/2025_09_24_182508_create_tenants_table.php --force

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Tabla tenants creada exitosamente" -ForegroundColor Green
} else {
    Write-Host "❌ Error creando tabla tenants" -ForegroundColor Red
    Write-Host "💡 Asegúrate de que MySQL esté corriendo y las credenciales sean correctas" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "👤 Creando tenant de ejemplo..." -ForegroundColor Yellow

# Crear tenant de prueba
php artisan tenant:create demo "Empresa Demo" demo --domain=demo.com

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Tenant demo creado exitosamente" -ForegroundColor Green
    Write-Host ""
    Write-Host "🌐 URLs de acceso:" -ForegroundColor Cyan
    
    # Leer BASE_DOMAIN del .env
    $baseDomain = (Get-Content .env | Where-Object { $_ -like "BASE_DOMAIN=*" } | ForEach-Object { $_.Split('=')[1] })
    
    Write-Host "   Gestión: https://$baseDomain/api/admin/tenants" -ForegroundColor White
    Write-Host "   Tenant Demo: https://demo.$baseDomain/api/usuarios/listUsers" -ForegroundColor White
} else {
    Write-Host "❌ Error creando tenant demo" -ForegroundColor Red
    Write-Host "💡 Revisar logs y credenciales MySQL" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🎉 Configuración completada!" -ForegroundColor Green
Write-Host ""
Write-Host "📚 Leer MULTITENANCY.md para más información" -ForegroundColor Cyan
Write-Host "🔧 Usar 'php artisan tenant:create' para crear más tenants" -ForegroundColor Cyan

Write-Host ""
Write-Host "🔍 Comandos útiles:" -ForegroundColor Yellow
Write-Host "   Listar tenants: php artisan tinker --execute='App\Models\Tenant::all()'" -ForegroundColor White
Write-Host "   Ver BD creadas: mysql -u root -p -e 'SHOW DATABASES LIKE \"tenant_%\"'" -ForegroundColor White