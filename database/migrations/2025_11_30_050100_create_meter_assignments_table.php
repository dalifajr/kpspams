<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('target_customers')->default(0);
            $table->unsignedInteger('completed_customers')->default(0);
            $table->decimal('total_volume', 10, 3)->default(0);
            $table->unsignedBigInteger('total_bill')->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->unique(['meter_period_id', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_assignments');
    }
};
