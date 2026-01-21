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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('purchase_order_id')->constrained();
                $table->foreignId('organization_id')->constrained();

                $table->decimal('amount', 12, 2);

                $table->string('payment_method');
                // cash | stripe | paypal | bank_transfer

                $table->string('payment_status');
                // pending | processing | partial | completed | failed | refunded

                $table->string('provider')->nullable();
                // stripe | paypal

                $table->string('provider_payment_id')->nullable();
                $table->string('provider_invoice_id')->nullable();
                $table->string('provider_invoice_number')->nullable();

                $table->json('provider_payload')->nullable();

                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
