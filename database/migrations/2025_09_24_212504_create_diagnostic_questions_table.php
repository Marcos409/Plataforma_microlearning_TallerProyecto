<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostic_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnostic_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->json('options');
            $table->integer('correct_answer');
            $table->integer('difficulty_level')->default(1);
            $table->string('topic');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostic_questions');
    }
};