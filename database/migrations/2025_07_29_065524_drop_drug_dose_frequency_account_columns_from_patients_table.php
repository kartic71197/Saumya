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
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'drug',
                'dose',
                'frequency',
                'account_number',
                'pa_expires',
                'paid',
                'our_cost',
                'pt_copay',
                'date_given',
                'profit'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('drug')->nullable();
            $table->string('dose')->nullable();
            $table->string('frequency')->nullable();
            $table->string('account_number')->nullable();
            $table->string('pa_expires')->nullable();
            $table->string('paid')->nullable();
            $table->string('our_cost')->nullable();
            $table->string('pt_copay')->nullable();
            $table->string('date_given')->nullable();
            $table->string('profit')->nullable();
        });
    }
};
