<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->unique();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('database_name');
            $table->string('db_username');
            $table->text('db_password'); // Encriptado
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('domain')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            
            // Índices para rendimiento
            $table->index('tenant_id');
            $table->index('subdomain');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
