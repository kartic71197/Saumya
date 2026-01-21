<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        if (Schema::hasTable('appointment_service_tag')) {
            return; // ✅ skip if already exists
        }

        Schema::create('appointment_service_tag', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_service_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('appointment_tag_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_service_tag');
    }
};
