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
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('appointment_date');
            $table->enum('visit_type', ['NEW_PATIENT', 'REPORT_VISIT'])->default('NEW_PATIENT');
            $table->integer('serial_number')->default(1);
            $table->string('token_number', 30)->nullable();
            $table->decimal('fee_amount', 10, 2)->default(75.00);
            $table->enum('payment_status', ['UNPAID', 'PAID'])->default('UNPAID');
            $table->dateTime('paid_at')->nullable();
            $table->enum('status', ['SCHEDULED', 'AWAITING_PAYMENT', 'CHECKED_IN', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('SCHEDULED');
            $table->string('type', 50)->default('CONSULTATION');
            $table->text('chief_complaint')->nullable();
            $table->text('notes')->nullable();
            $table->json('notification_payload')->nullable();
            $table->timestamps();

            $table->index(['appointment_date', 'provider_id', 'serial_number']);
            $table->index(['payment_status', 'status']);
        });

        Schema::create('encounters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->dateTime('encounter_date');
            $table->decimal('temperature_c', 4, 2)->nullable();
            $table->integer('heart_rate_bpm')->nullable();
            $table->integer('systolic_bp')->nullable();
            $table->integer('diastolic_bp')->nullable();
            $table->decimal('spo2_percentage', 4, 1)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->longText('subjective_notes')->nullable();
            $table->longText('objective_notes')->nullable();
            $table->longText('assessment_notes')->nullable();
            $table->longText('plan_notes')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->dateTime('signed_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'provider_id', 'encounter_date']);
        });

        Schema::create('encounter_diagnoses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->string('icd10_code', 20);
            $table->string('description', 255);
            $table->enum('type', ['PRIMARY', 'SECONDARY', 'CHRONIC'])->default('PRIMARY');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['encounter_id', 'icd10_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounter_diagnoses');
        Schema::dropIfExists('encounters');
        Schema::dropIfExists('appointments');
    }
};
