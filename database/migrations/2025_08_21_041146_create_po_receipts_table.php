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
        if (!Schema::hasTable('po_receipts')) {
        Schema::create('po_receipts', function (Blueprint $table) {
            $table->id();

            // Foreign key to purchase_orders
            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->onDelete('cascade');

            $table->string('product_id');

            // Quantities should be integers or decimals
            $table->integer('ordered_qty')->unsigned();
            $table->integer('received_qty')->unsigned();

            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            $table->date('date_received');

            // If received_by is a user_id, make it a foreign key
            $table->foreignId('received_by')->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_receipts');
    }
};
