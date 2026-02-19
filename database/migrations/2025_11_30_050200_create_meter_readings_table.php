<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meter_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('start_reading', 12, 3)->nullable();
            $table->decimal('end_reading', 12, 3)->nullable();
            $table->decimal('usage_m3', 12, 3)->default(0);
            $table->unsignedBigInteger('bill_amount')->default(0);
            $table->string('status', 20)->default('pending');
            $table->boolean('is_estimated')->default(false);
            $table->text('note')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->unique(['meter_period_id', 'customer_id']);
            $table->index(['meter_assignment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
