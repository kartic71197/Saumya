<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only create table if it doesn't exist
        if (!Schema::hasTable('field_reps')) {
            Schema::create('field_reps', function (Blueprint $table) {
                $table->id();

                // Relations
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();

                // Med Rep details
                $table->string('medrep_name');
                $table->string('medrep_phone')->nullable();
                $table->string('medrep_email')->nullable();

                // Soft delete flag for logical deletion
                $table->boolean('is_deleted')->default(false);

                $table->timestamps();

                // Optional foreign keys
                $table->foreign('organization_id')
                    ->references('id')
                    ->on('organizations')
                    ->nullOnDelete();

                $table->foreign('supplier_id')
                    ->references('id')
                    ->on('suppliers')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_reps');
    }
};
