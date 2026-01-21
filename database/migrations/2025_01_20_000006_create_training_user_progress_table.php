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
        if (!Schema::hasTable('training_user_progress')) {
            Schema::create('training_user_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // User ID
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreignId('video_id')->nullable()->constrained('training_videos')->onDelete('cascade');
                $table->foreignId('quiz_id')->nullable()->constrained('training_quizzes')->onDelete('cascade');
                $table->string('status')->default('not_started'); // not_started, in_progress, completed, failed
                $table->integer('progress_percentage')->default(0); // For videos: watch percentage
                $table->integer('score')->nullable(); // For quizzes: final score
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('quiz_answers')->nullable(); // Store quiz answers
                $table->timestamps();

                // Ensure unique combination of user_id and content
                $table->unique(['user_id', 'video_id'], 'unique_video_progress');
                $table->unique(['user_id', 'quiz_id'], 'unique_quiz_progress');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_user_progress');
    }
};
