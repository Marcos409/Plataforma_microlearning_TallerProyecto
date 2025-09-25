<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostic_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('diagnostic_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained('diagnostic_questions')->onDelete('cascade');
            $table->integer('selected_answer');
            $table->boolean('is_correct');
            $table->integer('time_spent')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostic_responses');
    }
};