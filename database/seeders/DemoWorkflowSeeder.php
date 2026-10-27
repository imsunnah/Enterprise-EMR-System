<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\EncounterDiagnosis;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTestCatalog;
use App\Models\MedicineCatalog;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\BillingEngine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoWorkflowSeeder extends Seeder
{
    /**
     * Optional sample workflow data: Demo patients, appointments, encounters, prescriptions, and lab orders.
     */
    public function run(): void
    {
        $admin = User::where('role', 'ADMIN')->first();
        $doctor1 = User::where('email', 'doctor@emr.local')->first();

        if (!$doctor1 || !$admin) {
            $this->command->warn('Base users not found. Please run DatabaseSeeder first.');
            return;
        }

        $medicineModels = MedicineCatalog::all();
        $labTestModels = LabTestCatalog::all();

        // 1. Primary Household Patient Account (Phone Authentication)
        $account = PatientAccount::create([
            'phone_number' => '+15551234567',
            'password_hash' => Hash::make('secret123'),
            'otp_code' => '123456',
            'otp_expires_at' => now()->addYear(),
            'is_verified' => true,
        ]);

        // Household Profiles
        $patientSelf = Patient::create([
            'account_id' => $account->id,
            'mrn' => 'MRN-2026-10492',
            'first_name' => 'Johnathan',
            'last_name' => 'Miller',
            'date_of_birth' => '1984-05-14',
            'gender' => 'MALE',
            'blood_group' => 'O+',
            'phone' => '+15551234567',
            'email' => 'john.miller@example.com',
            'address' => '742 Evergreen Terrace, Springfield',
            'relationship_to_account_holder' => 'SELF',
            'emergency_contact_name' => 'Claire Miller',
            'emergency_contact_phone' => '+15559876543',
            'insurance_provider' => 'Blue Cross Blue Shield HealthNet',
            'insurance_policy_number' => 'BCBS-9842104-A',
            'allergies' => 'Penicillin, Shellfish',
        ]);

        $patientSpouse = Patient::create([
            'account_id' => $account->id,
            'mrn' => 'MRN-2026-10493',
            'first_name' => 'Claire',
            'last_name' => 'Miller',
            'date_of_birth' => '1987-11-20',
            'gender' => 'FEMALE',
            'blood_group' => 'A+',
            'phone' => '+15551234567',
            'email' => 'claire.miller@example.com',
            'address' => '742 Evergreen Terrace, Springfield',
            'relationship_to_account_holder' => 'SPOUSE',
            'emergency_contact_name' => 'Johnathan Miller',
            'emergency_contact_phone' => '+15551234567',
            'insurance_provider' => 'Blue Cross Blue Shield HealthNet',
            'allergies' => 'None reported',
        ]);

        $patientChild = Patient::create([
            'account_id' => $account->id,
            'mrn' => 'MRN-2026-10494',
            'first_name' => 'Leo',
            'last_name' => 'Miller',
            'date_of_birth' => '2016-08-10',
            'gender' => 'MALE',
            'blood_group' => 'O+',
            'phone' => '+15551234567',
            'email' => 'leo.miller@example.com',
            'address' => '742 Evergreen Terrace, Springfield',
            'relationship_to_account_holder' => 'CHILD',
            'emergency_contact_name' => 'Johnathan Miller',
            'emergency_contact_phone' => '+15551234567',
            'insurance_provider' => 'Blue Cross Blue Shield HealthNet',
            'allergies' => 'Peanuts',
        ]);

        $patientWalkIn = Patient::create([
            'account_id' => null, // Standalone walk-in
            'mrn' => 'MRN-2026-20015',
            'first_name' => 'Robert',
            'last_name' => 'Sterling',
            'date_of_birth' => '1958-03-22',
            'gender' => 'MALE',
            'blood_group' => 'B+',
            'phone' => '+15554443322',
            'address' => '12 Elm Street, Springfield',
            'relationship_to_account_holder' => 'SELF',
            'emergency_contact_name' => 'Linda Sterling',
            'emergency_contact_phone' => '+15554443323',
            'insurance_provider' => 'Medicare Part B',
            'allergies' => 'Sulfa drugs',
        ]);

        // Appointments with Daily Serials & Tokens
        $app1 = Appointment::create([
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor1->id,
            'appointment_date' => now(),
            'visit_type' => 'NEW_PATIENT',
            'serial_number' => 1,
            'token_number' => 'TK-001',
            'fee_amount' => 75.00,
            'payment_status' => 'PAID',
            'paid_at' => now()->subHours(1),
            'status' => 'IN_PROGRESS',
            'type' => 'CONSULTATION',
            'chief_complaint' => 'Hypertension follow-up and chronic fatigue.',
        ]);
        BillingEngine::generateConsultationInvoice($app1, 'CREDIT_CARD', 'CC-AUTH-8821', 'Paid at front desk reception.');

        $app2 = Appointment::create([
            'patient_id' => $patientWalkIn->id,
            'provider_id' => $doctor1->id,
            'appointment_date' => now(),
            'visit_type' => 'NEW_PATIENT',
            'serial_number' => 2,
            'token_number' => 'TK-002',
            'fee_amount' => 75.00,
            'payment_status' => 'PAID',
            'paid_at' => now()->subMinutes(20),
            'status' => 'CHECKED_IN',
            'type' => 'WALK_IN',
            'chief_complaint' => 'Chest tightness on exertion and dizziness.',
        ]);
        BillingEngine::generateConsultationInvoice($app2, 'CASH', null, 'Cash paid at front desk.');

        $app3 = Appointment::create([
            'patient_id' => $patientSpouse->id,
            'provider_id' => $doctor1->id,
            'appointment_date' => now()->addHours(1),
            'visit_type' => 'REPORT_VISIT',
            'serial_number' => 3,
            'token_number' => 'TK-003',
            'fee_amount' => 30.00,
            'payment_status' => 'UNPAID',
            'status' => 'SCHEDULED',
            'type' => 'FOLLOW_UP',
            'chief_complaint' => 'Review of fasting lab test reports.',
        ]);

        // Clinical Encounter & EHR for Johnathan Miller
        $encounter = Encounter::create([
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor1->id,
            'appointment_id' => $app1->id,
            'encounter_date' => now(),
            'temperature_c' => 36.8,
            'heart_rate_bpm' => 74,
            'systolic_bp' => 134,
            'diastolic_bp' => 86,
            'spo2_percentage' => 98.5,
            'weight_kg' => 84.5,
            'height_cm' => 178.0,
            'bmi' => 26.7,
            'subjective_notes' => "Patient reports adherence to medications. Experiences afternoon fatigue. Denies chest pain or shortness of breath.",
            'objective_notes' => "Alert, S1/S2 audible, regular rhythm. Chest clear bilaterally. Abdomen soft, non-tender.",
            'assessment_notes' => "1. Essential hypertension (I10).\n2. Type 2 diabetes mellitus (E11.9).\n3. Hypercholesterolemia (E78.0).",
            'plan_notes' => "1. Refill Lisinopril and Metformin.\n2. Prescribed Comprehensive Lipid Panel and HbA1c.\n3. Return with lab reports in 2 weeks (Report Visit).",
            'is_signed' => true,
            'signed_at' => now(),
        ]);

        EncounterDiagnosis::create([
            'encounter_id' => $encounter->id,
            'icd10_code' => 'I10',
            'description' => 'Essential (primary) hypertension',
            'type' => 'PRIMARY',
        ]);
        EncounterDiagnosis::create([
            'encounter_id' => $encounter->id,
            'icd10_code' => 'E11.9',
            'description' => 'Type 2 diabetes mellitus without complications',
            'type' => 'SECONDARY',
        ]);

        $rx = Prescription::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor1->id,
            'notes' => 'Take with morning meals.',
            'status' => 'ACTIVE',
        ]);

        $lisinopril = $medicineModels->where('generic_name', 'Lisinopril')->first() ?? $medicineModels->first();
        if ($lisinopril) {
            PrescriptionItem::create([
                'prescription_id' => $rx->id,
                'medicine_id' => $lisinopril->id,
                'dosage' => '1 tablet',
                'frequency' => 'Once daily (morning)',
                'duration_days' => 30,
                'quantity' => 30,
                'unit_price' => $lisinopril->unit_price,
                'discount_percentage' => $lisinopril->discount_percentage,
                'discount_amount' => round(($lisinopril->unit_price * 0.10), 2),
                'total_price' => round($lisinopril->unit_price - round(($lisinopril->unit_price * 0.10), 2), 2),
                'instructions' => 'Take with a glass of water.',
            ]);
        }

        $labOrder = LabOrder::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor1->id,
            'clinical_notes' => 'Annual lipid and glycemic control evaluation.',
            'priority' => 'ROUTINE',
            'status' => 'ORDERED',
        ]);

        foreach ($labTestModels->take(3) as $test) {
            $discountAmt = round($test->standard_price * ($test->discount_percentage / 100), 2);
            LabOrderItem::create([
                'lab_order_id' => $labOrder->id,
                'lab_test_id' => $test->id,
                'standard_price' => $test->standard_price,
                'discount_percentage' => $test->discount_percentage,
                'discount_amount' => $discountAmt,
                'net_price' => round($test->standard_price - $discountAmt, 2),
                'billing_status' => 'UNBILLED',
                'result_status' => 'PENDING',
            ]);
        }

        AuditLogger::log('INITIALIZE_HMS_DEMO_SEED', 'System', $admin->id, ['workflow' => 'Demo Patient Data']);
    }
}
