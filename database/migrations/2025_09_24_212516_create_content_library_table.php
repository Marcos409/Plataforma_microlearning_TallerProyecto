<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_library', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'pdf', 'article', 'quiz', 'interactive']);
            $table->string('subject_area');
            $table->string('topic');
            $table->integer('difficulty_level')->default(1);
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->integer('estimated_duration')->default(0);
            $table->json('tags')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_library');
    }
};