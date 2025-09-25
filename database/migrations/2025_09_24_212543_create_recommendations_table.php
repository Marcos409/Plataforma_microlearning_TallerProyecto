<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_id')->constrained('content_library')->onDelete('cascade');
            $table->enum('type', ['diagnostic_based', 'ai_predicted', 'teacher_assigned']);
            $table->text('reason')->nullable();
            $table->integer('priority')->default(1);
            $table->boolean('is_viewed')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};