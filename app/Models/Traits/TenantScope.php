<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait TenantScope
{
    /**
     * Boot del trait - se ejecuta automáticamente cuando se usa el trait
     */
    protected static function bootTenantScope()
    {
        // Aplicar scope global para filtrar por tenant_id
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = app('currentTenant', 'default');
            $builder->where('tenant_id', $tenantId);
        });

                // Al crear un nuevo modelo, asignar automáticamente el tenant_id
        static::creating(function (Model $model) {
            if (!$model->tenant_id) {
                $model->tenant_id = app('currentTenant', 'default');
            }
        });
    }

    /**
     * Scope para obtener todos los registros sin filtro de tenant (solo para admin)
     */
    public function scopeWithoutTenant(Builder $query)
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope para filtrar por un tenant específico
     */
    public function scopeForTenant(Builder $query, $tenantId)
    {
        return $query->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }
}
