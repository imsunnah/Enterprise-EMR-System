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
        Schema::create('medicine_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('brand_name', 200);
            $table->string('generic_name', 200);
            $table->string('dosage_form', 100);
            $table->string('strength', 50);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['brand_name', 'generic_name', 'is_active']);
        });

        Schema::create('lab_test_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('test_code', 50)->unique();
            $table->string('test_name', 200);
            $table->string('category', 100);
            $table->decimal('standard_price', 10, 2)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->string('specimen_type', 100)->nullable();
            $table->integer('turnaround_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['test_code', 'category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_catalog');
        Schema::dropIfExists('medicine_catalog');
    }
};
