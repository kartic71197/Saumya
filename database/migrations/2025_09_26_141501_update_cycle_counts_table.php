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
        Schema::table('cycle_counts', function (Blueprint $table) {
            // From first migration
            if (!Schema::hasColumn('cycle_counts', 'count_id')) {
                $table->string('count_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('cycle_counts', 'organization_id')) {
                $table->string('organization_id')->nullable()->after('expiry_date');
            }
            if (!Schema::hasColumn('cycle_counts', 'location_id')) {
                $table->string('location_id')->nullable()->after('organization_id');
            }

            // From second migration
            if (!Schema::hasColumn('cycle_counts', 'admin_updated_qty')) {
                $table->decimal('admin_updated_qty', 10, 2)->nullable()->after('counted_qty');
            }
            if (!Schema::hasColumn('cycle_counts', 'cycle_id')) {
                $table->foreignId('cycle_id')
                    ->nullable()
                    ->constrained('cycles')
                    ->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cycle_counts', function (Blueprint $table) {
            if (Schema::hasColumn('cycle_counts', 'count_id')) {
                $table->dropColumn('count_id');
            }
            if (Schema::hasColumn('cycle_counts', 'organization_id')) {
                $table->dropColumn('organization_id');
            }
            if (Schema::hasColumn('cycle_counts', 'location_id')) {
                $table->dropColumn('location_id');
            }
            if (Schema::hasColumn('cycle_counts', 'admin_updated_qty')) {
                $table->dropColumn('admin_updated_qty');
            }
            if (Schema::hasColumn('cycle_counts', 'cycle_id')) {
                $table->dropConstrainedForeignId('cycle_id');
            }
        });
    }
};
