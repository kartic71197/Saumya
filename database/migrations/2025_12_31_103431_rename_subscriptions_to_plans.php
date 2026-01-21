<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename only if subscriptions exists AND plans does not exist
        if (Schema::hasTable('subscriptions') && ! Schema::hasTable('plans')) {
            Schema::rename('subscriptions', 'plans');
        }
    }

    public function down(): void
    {
        // Rollback only if plans exists AND subscriptions does not exist
        if (Schema::hasTable('plans') && ! Schema::hasTable('subscriptions')) {
            Schema::rename('plans', 'subscriptions');
        }
    }
};
