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
        Schema::table('golongan_non_air_fees', function (Blueprint $table) {
            if (Schema::hasColumn('golongan_non_air_fees', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('golongan_non_air_fees', function (Blueprint $table) {
            if (! Schema::hasColumn('golongan_non_air_fees', 'name')) {
                $table->string('name')->after('golongan_id');
            }
        });
    }
};
