<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_path_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_id')->constrained('content_library')->onDelete('cascade');
            $table->integer('order_sequence');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_path_contents');
    }
};