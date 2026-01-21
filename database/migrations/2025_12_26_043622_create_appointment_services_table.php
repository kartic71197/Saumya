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
        if (!Schema::hasTable('appointment_services')) {
            Schema::create('appointment_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->foreignId('appointment_category_id')
                    ->constrained('appointment_categories')
                    ->cascadeOnDelete();

                $table->string('name');
                $table->text('description')->nullable();

                // Duration in minutes (15, 30, 45 etc.)
                $table->unsignedInteger('duration');

                // Price (supports decimals)
                $table->decimal('price', 10, 2);

                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_services');
    }
};
