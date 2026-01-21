<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_to_locations', function (Blueprint $table) {
            if (Schema::hasColumn('bill_to_locations', 'default_billing_location')) {
                $table->dropColumn('default_billing_location');
            }

            if (Schema::hasColumn('bill_to_locations', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });

        Schema::table('ship_to_locations', function (Blueprint $table) {
            if (Schema::hasColumn('ship_to_locations', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bill_to_locations', function (Blueprint $table) {
            $table->boolean('default_billing_location')->default(false);
            $table->boolean('is_default')->default(false);
        });

        Schema::table('ship_to_locations', function (Blueprint $table) {
            $table->boolean('is_default')->default(false);
        });
    }
};
