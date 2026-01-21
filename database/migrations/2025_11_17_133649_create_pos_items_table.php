<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // Skip if table already exists
        if (Schema::hasTable('pos')) {
            return;
        }

        Schema::create('pos', function (Blueprint $table) {
            $table->id();

            /* ----------------------------
             |  MANUAL & SAFE FOREIGN KEYS
             |----------------------------- */

            // organization_id → organizations.id
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')
                ->references('id')->on('organizations')
                ->cascadeOnDelete();

            // location_id → locations.id
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')
                ->references('id')->on('locations')
                ->nullOnDelete();

            // customer_id → pos_customers.id
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')
                ->references('id')->on('pos_customers')
                ->nullOnDelete();

            // created_by → users.id
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            /* ----------------------------
             |  NORMAL FIELDS
             |----------------------------- */

            $table->enum('payment_method', ['cash', 'card', 'upi'])->default('cash');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);

            $table->timestamp('sale_date')->useCurrent();
            $table->timestamps();

            /* ----------------------------
             |  INDEXES
             |----------------------------- */

            $table->index(['organization_id', 'sale_date']);
            $table->index(['location_id', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos');
    }
};
