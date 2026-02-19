<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 20)->unique();
            $table->string('name');
            $table->string('address_short')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('golongan', 80)->nullable();
            $table->unsignedSmallInteger('family_members')->default(0);
            $table->decimal('meter_reading', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'customer_id')) {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('area_id')
                    ->constrained('customers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'customer_id')) {
                $table->dropConstrainedForeignId('customer_id');
            }
        });

        Schema::dropIfExists('customers');
    }
};
