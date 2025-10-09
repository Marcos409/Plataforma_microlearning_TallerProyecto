<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ml_analysis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('diagnostico')->nullable();
            $table->string('ruta_aprendizaje')->nullable();
            $table->string('nivel_riesgo')->nullable();
            $table->json('metricas')->nullable();
            $table->json('recomendaciones')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ml_analysis');
    }
};