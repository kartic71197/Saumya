<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SAFETY: Do nothing if the column does not exist
        if (
            Schema::hasColumn('purchase_orders', 'purchase_oder_number') &&
            !Schema::hasColumn('purchase_orders', 'purchase_order_number')
        ) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->renameColumn(
                    'purchase_oder_number',
                    'purchase_order_number'
                );
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn('purchase_orders', 'purchase_order_number') &&
            !Schema::hasColumn('purchase_orders', 'purchase_oder_number')
        ) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->renameColumn(
                    'purchase_order_number',
                    'purchase_oder_number'
                );
            });
        }
    }
};
