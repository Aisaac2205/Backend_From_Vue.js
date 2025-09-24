<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Para login con Auth
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\TenantScope;


class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, TenantScope;

    protected $table = 'usuarios'; // Nombre exacto de la tabla

    protected $fillable = [
        'tenant_id', // Agregar tenant_id para multitenancy
        'nombre',
        'email',
        'password',
        'rol',
    ];

    // Ocultar campos sensibles al devolver el modelo en JSON
    protected $hidden = [
        'password',
    ];

    // Relación con Tareas
    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'user_id');
    }
}
