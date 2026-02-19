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
        if (! Schema::hasColumn('golongans', 'min_usage')) {
            Schema::table('golongans', function (Blueprint $table) {
                $table->decimal('min_usage', 8, 2)->default(0)->after('name');
            });
        }

        if (! Schema::hasColumn('golongans', 'max_usage_per_person')) {
            Schema::table('golongans', function (Blueprint $table) {
                $table->decimal('max_usage_per_person', 8, 2)->nullable()->after('min_usage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('golongans', 'max_usage_per_person')) {
            Schema::table('golongans', function (Blueprint $table) {
                $table->dropColumn('max_usage_per_person');
            });
        }

        if (Schema::hasColumn('golongans', 'min_usage')) {
            Schema::table('golongans', function (Blueprint $table) {
                $table->dropColumn('min_usage');
            });
        }
    }
};
