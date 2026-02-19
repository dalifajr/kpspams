<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('golongan_tariffs')) {
            return;
        }

        Schema::create('golongan_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('golongan_id')->constrained()->cascadeOnDelete();
            $table->decimal('meter_start', 8, 2)->default(0);
            $table->decimal('meter_end', 8, 2)->nullable();
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('golongan_tariffs');
    }
};
