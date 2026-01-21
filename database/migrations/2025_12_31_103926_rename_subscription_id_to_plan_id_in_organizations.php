<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('organizations')) {
            Schema::table('organizations', function (Blueprint $table) {

                if (Schema::hasColumn('organizations', 'subscription_id')
                    && ! Schema::hasColumn('organizations', 'plan_id')) {
                    $table->renameColumn('subscription_id', 'plan_id');
                }

                if (Schema::hasColumn('organizations', 'subscription_valid')
                    && ! Schema::hasColumn('organizations', 'plan_valid')) {
                    $table->renameColumn('subscription_valid', 'plan_valid');
                }

            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('organizations')) {
            Schema::table('organizations', function (Blueprint $table) {

                if (Schema::hasColumn('organizations', 'plan_id')
                    && ! Schema::hasColumn('organizations', 'subscription_id')) {
                    $table->renameColumn('plan_id', 'subscription_id');
                }

                if (Schema::hasColumn('organizations', 'plan_valid')
                    && ! Schema::hasColumn('organizations', 'subscription_valid')) {
                    $table->renameColumn('plan_valid', 'subscription_valid');
                }

            });
        }
    }
};
