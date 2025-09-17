<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait TenantScope
{
    protected static function bootTenantScope()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Aquí podrías filtrar por un campo tenant_id si usas single DB
            // En este caso, como es multi DB, no es necesario
        });
    }

    // Si necesitas cambiar la conexión dinámicamente
    public function setTenantConnection($connection)
    {
        $this->setConnection($connection);
        return $this;
    }
}
