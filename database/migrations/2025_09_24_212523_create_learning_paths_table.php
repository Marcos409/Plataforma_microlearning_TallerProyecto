<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject_area');
            $table->json('critical_topics')->nullable();
            $table->integer('total_contents')->default(0);
            $table->integer('completed_contents')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'paused'])->default('active');
            $table->timestamp('estimated_completion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_paths');
    }
};