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
        Schema::create('patient_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone_number', 25)->unique();
            $table->string('password_hash')->nullable();
            $table->string('otp_code', 10)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->nullable()->constrained('patient_accounts')->nullOnDelete();
            $table->string('mrn', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('date_of_birth');
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHER'])->default('MALE');
            $table->string('blood_group', 10)->nullable();
            $table->string('phone', 25);
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 25)->nullable();
            $table->string('relationship_to_account_holder', 50)->default('SELF');
            $table->string('insurance_provider', 150)->nullable();
            $table->string('insurance_policy_number', 100)->nullable();
            $table->text('allergies')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'mrn']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
        Schema::dropIfExists('patient_accounts');
    }
};
