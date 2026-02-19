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
        Schema::table('golongans', function (Blueprint $table) {
            if (Schema::hasColumn('golongans', 'max_usage_per_person')) {
                $table->dropColumn('max_usage_per_person');
            }

            if (Schema::hasColumn('golongans', 'min_usage')) {
                $table->dropColumn('min_usage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('golongans', function (Blueprint $table) {
            if (! Schema::hasColumn('golongans', 'min_usage')) {
                $table->decimal('min_usage', 8, 2)->default(0)->after('name');
            }

            if (! Schema::hasColumn('golongans', 'max_usage_per_person')) {
                $table->decimal('max_usage_per_person', 8, 2)->nullable()->after('min_usage');
            }
        });
    }
};
