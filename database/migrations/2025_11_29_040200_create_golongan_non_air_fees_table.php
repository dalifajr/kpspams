<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('golongan_non_air_fees')) {
            return;
        }

        Schema::create('golongan_non_air_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('golongan_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('golongan_non_air_fees');
    }
};
