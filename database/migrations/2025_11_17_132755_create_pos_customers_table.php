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

        if (Schema::hasTable('pos_customers')) {
            return;
        }
        Schema::create('pos_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['organization_id', 'phone']);
            $table->index(['organization_id', 'email']);

            // Unique constraint per organization
            $table->unique(['organization_id', 'phone']);
            $table->unique(['organization_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_customers');
    }
};
