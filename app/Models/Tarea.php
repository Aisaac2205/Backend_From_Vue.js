<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TenantScope;


class Tarea extends Model
{
    use HasFactory, TenantScope;

    // Usar la conexión dinámica del tenant
    protected $connection = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        // Asignar la conexión actual del tenant
        $this->connection = config('database.default');
    }

    protected $fillable = [
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
