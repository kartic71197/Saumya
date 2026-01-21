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
        Schema::create('edi810s', function (Blueprint $table) {
            $table->id();
            $table->string('po_number');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('shipped_date');
            $table->time('time');
            $table->string('scac');
            $table->string('carrier_info');
            $table->string('transportation_method');
            $table->string('reference_qualifier');
            $table->string('reference_id');
            $table->string('product_code');
            $table->string('product_description');
            $table->string('unit');
            $table->decimal('price', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total_amount_due', 10, 2);
            $table->decimal('taxPercent', 5, 2);
            $table->integer('qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edi810s');
    }
};
