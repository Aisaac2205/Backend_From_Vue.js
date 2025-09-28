<?php

namespace App\Traits;

use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait TenantScope para demostración de arquitectura multitenant
 * 
 * NOTA: Este trait es solo para demostración académica.
 * NO está implementado en los modelos existentes y NO afecta la funcionalidad actual.
 */
trait TenantScope
{
    /**
     * Boot del trait - se ejecuta cuando se inicializa el modelo
     */
    protected static function bootTenantScope()
    {
        // En una implementación real, esto agregaría automáticamente
        // filtros de tenant a todas las consultas
        
        /*
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantService = app(TenantService::class);
            
            if ($tenantService->hasTenant()) {
                $builder->where('tenant_id', $tenantService->getCurrentTenantId());
            }
        });

        // Agregar tenant_id automáticamente al crear registros
        static::creating(function ($model) {
            $tenantService = app(TenantService::class);
            
            if ($tenantService->hasTenant() && !$model->tenant_id) {
                $model->tenant_id = $tenantService->getCurrentTenantId();
            }
        });
        */
    }

    /**
     * Relación con el tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Scope para filtrar por tenant específico
     */
    public function scopeForTenant(Builder $query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope para el tenant actual
     */
    public function scopeForCurrentTenant(Builder $query)
    {
        $tenantService = app(TenantService::class);
        
        if ($tenantService->hasTenant()) {
            return $query->where('tenant_id', $tenantService->getCurrentTenantId());
        }
        
        return $query;
    }

    /**
     * Verificar si el modelo pertenece al tenant actual
     */
    public function belongsToCurrentTenant()
    {
        $tenantService = app(TenantService::class);
        
        if (!$tenantService->hasTenant()) {
            return true; // Sin tenant activo, permitir acceso
        }
        
        return $this->tenant_id === $tenantService->getCurrentTenantId();
    }

    /**
     * Verificar si el modelo pertenece a un tenant específico
     */
    public function belongsToTenant($tenantId)
    {
        return $this->tenant_id === $tenantId;
    }
}