<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_reading_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meter_period_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month');
            $table->unsignedBigInteger('water_usage_amount')->default(0);
            $table->unsignedBigInteger('admin_fee')->default(0);
            $table->unsignedBigInteger('other_fees')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('status', 20)->default('draft'); // draft, published, partial, paid
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['meter_reading_id']);
            $table->index(['customer_id', 'status']);
            $table->index(['meter_period_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
