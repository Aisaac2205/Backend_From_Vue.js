<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TenantScope;


class Tarea extends Model
{
    use HasFactory, TenantScope;

    protected $fillable = [
        'tenant_id', // Agregar tenant_id para multitenancy
        'titulo',
        'descripcion',
        'estado',
        'fecha_vencimiento',
        'user_id'
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    // Relación con Usuario
    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
