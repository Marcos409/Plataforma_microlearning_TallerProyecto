<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tu tabla 'roles' ahora se asegura de tener los campos necesarios para Spatie.
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name')->default('web'); // ¡Campo REQUERIDO por el paquete!
            $table->string('description')->nullable();
            $table->timestamps();
            
            // Clave única compuesta para evitar duplicados en el paquete de permisos
            $table->unique(['name', 'guard_name']); 
        });

        // Agrega el constraint de foreign key a la tabla 'users'.
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
        
        Schema::dropIfExists('roles');
    }
};
