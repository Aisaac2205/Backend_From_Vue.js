<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Para login con Auth
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios'; // Nombre exacto de la tabla

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
