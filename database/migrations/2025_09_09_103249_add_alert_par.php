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
        Schema::table('mycatalogs', function (Blueprint $table) {
            $table->string('alert_quantity')->default(3)->after('location_id');
            $table->string('par_quantity')->default(10)->after('alert_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mycatalogs', function (Blueprint $table) {
            $table->dropColumn('alert_quantity');
            $table->dropColumn('par_quantity');
        });
    }
};
