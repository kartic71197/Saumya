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
        Schema::create('training_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('training_chapters')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_url'); // S3 URL for the PDF/document
            $table->string('file_name');
            $table->string('file_type'); // pdf, doc, docx, etc.
            $table->integer('file_size')->nullable(); // Size in bytes
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_notes');
    }
}; 