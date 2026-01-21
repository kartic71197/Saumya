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
        Schema::table('mycatalogs', function (Blueprint $table) {
            $table->dropColumn(['product_price', 'product_cost', 'category_id']);
            // Add new column
            $table->string('location_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mycatalogs', function (Blueprint $table) {
            $table->dropColumn('location_id');

            // Restore old columns
            $table->decimal('product_price', 10, 2)->nullable();
            $table->decimal('product_cost', 10, 2)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
        });
    }
};
