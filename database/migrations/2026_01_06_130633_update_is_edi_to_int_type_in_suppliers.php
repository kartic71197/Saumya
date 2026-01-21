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
        Schema::table('suppliers', function (Blueprint $table) {
           // Add new column
            $table->string('int_type')->default('NONE')->after('is_edi');
        });
         // Move existing EDI suppliers to new column
        DB::statement("
            UPDATE suppliers
            SET int_type = 'EDI'
            WHERE is_edi = 1
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('int_type');
        });
    }
};
