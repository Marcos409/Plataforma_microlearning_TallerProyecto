<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject_area');
            $table->string('topic');
            $table->integer('total_activities')->default(0);
            $table->integer('completed_activities')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->integer('total_time_spent')->default(0);
            $table->timestamp('last_activity')->nullable();
            $table->json('weak_areas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};