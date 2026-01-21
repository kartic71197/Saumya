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
        if (!Schema::hasTable('cycle_counts')) {
            Schema::create('cycle_counts', function (Blueprint $table) {
                $table->id();
                $table->string('count_id');
                $table->string('product_id');
                $table->string('batch_number')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('organization_id');
                $table->string('location_id');
                $table->decimal('expected_qty', 10, 2)->default(0);
                $table->decimal('counted_qty', 10, 2)->nullable();
                $table->decimal('variance', 10, 2)->nullable()->comment('counted_qty - expected_qty');
                $table->string('cycle_name')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['pending', 'completed', 'approved', 'rejected'])->default('pending');
                $table->string('user_id')->nullable();
                $table->timestamp('counted_at')->nullable();
                $table->timestamps();
                // Indexes
                $table->index(['product_id', 'status']);
                $table->index('cycle_name');
                $table->index('counted_at');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycle_counts');
    }
};
