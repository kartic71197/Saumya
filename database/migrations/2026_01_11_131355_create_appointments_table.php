<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignId('appointment_category_id')->constrained()->cascadeOnDelete();

                $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

                $table->date('appointment_date');
                $table->time('start_time');
                $table->time('end_time');

                $table->decimal('price', 10, 2)->default(0);
                $table->integer('duration_minutes');

                $table->string('status')->default('scheduled');

                $table->foreignId('created_by')->constrained('users')->nullOnDelete();

                $table->timestamps();

                // Optional but recommended indexes
                $table->index(['organization_id', 'appointment_date']);
                $table->index(['staff_id', 'appointment_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
