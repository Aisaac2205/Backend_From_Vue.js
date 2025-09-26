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
        'nombre',
        'email',
        'password',
        'rol',
    ];

    // Ocultar campos sensibles al devolver el modelo en JSON
    protected $hidden = [
        'password',
    ];

    // Accesores para compatibilidad con el frontend
    public function getNameAttribute()
    {
        return $this->nombre;
    }
    
    public function getRoleAttribute()
    {
        return $this->rol;
    }
    
    // Mutadores para compatibilidad
    public function setNameAttribute($value)
    {
        $this->attributes['nombre'] = $value;
    }
    
    public function setRoleAttribute($value)
    {
        $this->attributes['rol'] = $value;
    }

    // Relación con Tareas
    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'user_id');
    }
}
