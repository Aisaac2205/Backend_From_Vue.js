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

    // Usar la conexión dinámica del tenant
    protected $connection = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        // Asignar la conexión actual del tenant
        $this->connection = config('database.default');
    }

    protected $table = 'usuarios'; // Nombre exacto de la tabla

    protected $fillable = [
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
