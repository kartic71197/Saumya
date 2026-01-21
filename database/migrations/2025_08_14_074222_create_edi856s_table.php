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
        Schema::create('edi856s', function (Blueprint $table) {
            $table->id();
            $table->string('poNumber');
            $table->string('internalRefNumber');
            $table->date('date');
            $table->time('time');
            $table->string('SCAC');
            $table->string('carrier');
            $table->string('invoiceNumber');
            $table->string('product_code');
            $table->string('product_desc');
            $table->integer('unitShipped');
            $table->integer('units');
            $table->string('status');
            $table->date('shippedDate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edi856s');
    }
};
