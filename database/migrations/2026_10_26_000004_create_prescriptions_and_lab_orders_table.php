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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('ACTIVE');
            $table->timestamps();

            $table->index(['patient_id', 'encounter_id']);
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignUuid('medicine_id')->constrained('medicine_catalog');
            $table->string('dosage', 100);
            $table->string('frequency', 100);
            $table->integer('duration_days');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->text('clinical_notes')->nullable();
            $table->enum('priority', ['ROUTINE', 'URGENT', 'STAT'])->default('ROUTINE');
            $table->enum('status', ['ORDERED', 'COLLECTED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('ORDERED');
            $table->timestamps();

            $table->index(['patient_id', 'encounter_id']);
        });

        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->foreignUuid('lab_test_id')->constrained('lab_test_catalog');
            $table->decimal('standard_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('net_price', 10, 2);
            $table->enum('billing_status', ['UNBILLED', 'BILLED', 'DECLINED'])->default('UNBILLED');
            $table->uuid('invoice_id')->nullable();
            $table->text('result_value')->nullable();
            $table->string('reference_range', 100)->nullable();
            $table->string('result_status', 30)->default('PENDING');
            $table->text('result_notes')->nullable();
            $table->timestamps();

            $table->index(['billing_status', 'invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_order_items');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
    }
};
