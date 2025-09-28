<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para tabla tenants - SOLO PARA DEMOSTRACIÓN
 * 
 * NOTA: Esta migración es solo para demostración académica.
 * NO debe ejecutarse ya que no se implementará el sistema multitenant.
 * Está incluida solo para mostrar la estructura de datos que usaría.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // IMPORTANTE: Esta migración NO debe ejecutarse
        // Es solo para demostración de cómo sería la estructura
        
        /*
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('subdominio')->unique();
            $table->string('database_name');
            $table->string('database_host')->default('127.0.0.1');
            $table->integer('database_port')->default(3306);
            $table->string('database_username');
            $table->string('database_password');
            $table->boolean('estado')->default(true);
            $table->json('configuracion')->nullable();
            $table->timestamp('fecha_creacion');
            $table->timestamp('fecha_expiracion')->nullable();
            $table->timestamps();
            
            $table->index(['subdominio', 'estado']);
        });
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('tenants');
    }
};