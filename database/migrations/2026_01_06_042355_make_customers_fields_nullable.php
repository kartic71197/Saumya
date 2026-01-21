<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->change();
            $table->string('customer_phone', 20)->nullable()->change();
            $table->string('customer_address')->nullable()->change();
            $table->string('customer_city')->nullable()->change();
            $table->string('customer_state')->nullable()->change();
            $table->string('customer_pin_code')->nullable()->change();
            $table->string('customer_country')->nullable()->change();
            $table->boolean('customer_is_active')->nullable()->default(true)->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_email')->nullable(false)->change();
            $table->string('customer_phone', 20)->nullable(false)->change();
            $table->string('customer_address')->nullable(false)->change();
            $table->string('customer_city')->nullable(false)->change();
            $table->string('customer_state')->nullable(false)->change();
            $table->string('customer_pin_code')->nullable(false)->change();
            $table->string('customer_country')->nullable(false)->change();
            $table->boolean('customer_is_active')->nullable(false)->default(true)->change();
        });
    }
};
