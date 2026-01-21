<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // Drop FK only if it exists
        $fkExists = DB::selectOne("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'appointments'
          AND COLUMN_NAME = 'appointment_category_id'
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ");

        Schema::table('appointments', function (Blueprint $table) use ($fkExists) {

            if ($fkExists) {
                $table->dropForeign(['appointment_category_id']);
            }

            if (Schema::hasColumn('appointments', 'appointment_category_id')) {
                $table->dropColumn('appointment_category_id');
            }

            if (!Schema::hasColumn('appointments', 'service_ids')) {
                $table->json('service_ids')->nullable()->after('organization_id');
            }
        });
    }


    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {

            // 1️⃣ Remove service_ids if exists
            if (Schema::hasColumn('appointments', 'service_ids')) {
                $table->dropColumn('service_ids');
            }

            // 2️⃣ Restore appointment_category_id if missing
            if (!Schema::hasColumn('appointments', 'appointment_category_id')) {
                $table->foreignId('appointment_category_id')
                    ->nullable()
                    ->after('organization_id')
                    ->constrained()
                    ->cascadeOnDelete();
            }
        });
    }
};
