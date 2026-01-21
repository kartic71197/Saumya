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
        Schema::create('edi855s', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order');
            $table->date('ack_date');
            $table->string('bill_to');
            $table->string('ship_to');
            $table->string('product_name');
            $table->string('product_code');
            $table->integer('ordered_qty');
            $table->string('ordered_unit');
            $table->decimal('unit_price', 10, 2);
            $table->integer('ack_qty');
            $table->string('ack_unit');
            $table->boolean('ack')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edi855s');
    }
};
