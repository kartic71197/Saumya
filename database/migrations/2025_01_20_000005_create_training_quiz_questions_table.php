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
        Schema::create('training_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('training_quizzes')->onDelete('cascade');
            $table->text('question');
            $table->string('question_type')->default('multiple_choice'); // multiple_choice, true_false, essay
            $table->json('options')->nullable(); // For multiple choice questions
            $table->string('correct_answer');
            $table->text('explanation')->nullable(); // Explanation for the correct answer
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_quiz_questions');
    }
}; 