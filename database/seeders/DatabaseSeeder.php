<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\EncounterDiagnosis;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete HMS workflow data.
     */
    public function run(): void
    {
        // 1. Staff Users & Roles
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@emr.local',
            'password' => Hash::make('secret123'),
            'role' => 'ADMIN',
            'department' => 'Hospital Governance',
            'phone' => '+15550000001',
            'is_active' => true,
        ]);

        $doctor1 = User::create([
            'name' => 'Dr. Sarah Jenkins, MD',
            'email' => 'doctor@emr.local',
            'password' => Hash::make('secret123'),
            'role' => 'DOCTOR',
            'department' => 'Internal Medicine',
            'specialization' => 'Cardiologist & General Physician',
            'license_number' => 'MD-749219',
            'consultation_fee' => 75.00, // New Patient Fee
            'followup_fee' => 30.00,     // Report Visit / Follow-up Fee
            'phone' => '+15550000002',
            'is_active' => true,
        ]);

        $doctor2 = User::create([
            'name' => 'Dr. David Hayes, MD',
            'email' => 'hayes@emr.local',
            'password' => Hash::make('secret123'),
            'role' => 'DOCTOR',
            'department' => 'Neurology',
            'specialization' => 'Consultant Neurologist',
            'license_number' => 'MD-991204',
            'consultation_fee' => 90.00,
            'followup_fee' => 40.00,
            'phone' => '+15550000005',
            'is_active' => true,
        ]);

        $receptionist = User::create([
            'name' => 'Sarah Connor',
            'email' => 'receptionist@emr.local',
            'password' => Hash::make('secret123'),
            'role' => 'RECEPTIONIST',
            'department' => 'Outpatient Registration & Cashier',
            'specialization' => 'Front Desk Cashier',
            'phone' => '+15550000006',
            'is_active' => true,
        ]);

        $nurse = User::create([
            'name' => 'Emily Clark, RN',
            'email' => 'nurse@emr.local',
            'password' => Hash::make('secret123'),
            'role' => 'NURSE',
            'department' => 'Triage & Inpatient Care',
            'specialization' => 'Triage Specialist',
            'license_number' => 'RN-382910',
            'phone' => '+15550000003',
            'is_active' => true,
        ]);

        $biller = User::create([
            'name' => 'Michael Vance',
            'email' => 'biller@emr.local',
            'password' => Hash::make('secret123'),
            'role' => 'BILLER',
            'department' => 'Revenue Cycle & Diagnostic Counter',
            'specialization' => 'Diagnostic Billing Specialist',
            'phone' => '+15550000004',
            'is_active' => true,
        ]);

        // 2. Medicine Catalog
        $medicines = [
            [
                'brand_name' => 'Amoxil',
                'generic_name' => 'Amoxicillin',
                'dosage_form' => 'Capsule',
                'strength' => '500mg',
                'unit_price' => 18.50,
                'discount_percentage' => 10.00,
                'stock_quantity' => 250,
                'description' => 'Broad spectrum beta-lactam antibiotic for bacterial infections.',
            ],
            [
                'brand_name' => 'Glucophage',
                'generic_name' => 'Metformin HCl',
                'dosage_form' => 'Tablet',
                'strength' => '850mg',
                'unit_price' => 12.00,
                'discount_percentage' => 15.00,
                'stock_quantity' => 420,
                'description' => 'First-line medication for the treatment of type 2 diabetes mellitus.',
            ],
            [
                'brand_name' => 'Lipitor',
                'generic_name' => 'Atorvastatin Calcium',
                'dosage_form' => 'Film-coated Tablet',
                'strength' => '20mg',
                'unit_price' => 24.00,
                'discount_percentage' => 5.00,
                'stock_quantity' => 180,
                'description' => 'HMG-CoA reductase inhibitor for lipid management and CV risk reduction.',
            ],
            [
                'brand_name' => 'Zestril',
                'generic_name' => 'Lisinopril',
                'dosage_form' => 'Tablet',
                'strength' => '10mg',
                'unit_price' => 15.00,
                'discount_percentage' => 10.00,
                'stock_quantity' => 310,
                'description' => 'ACE inhibitor for management of essential hypertension.',
            ],
            [
                'brand_name' => 'Prilosec',
                'generic_name' => 'Omeprazole',
                'dosage_form' => 'Delayed-release Capsule',
                'strength' => '40mg',
                'unit_price' => 14.50,
                'discount_percentage' => 0.00,
                'stock_quantity' => 150,
                'description' => 'Proton-pump inhibitor for acid reflux.',
            ],
            [
                'brand_name' => 'Tylenol Extra',
                'generic_name' => 'Paracetamol',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'unit_price' => 5.00,
                'discount_percentage' => 0.00,
                'stock_quantity' => 1000,
                'description' => 'Analgesic and antipyretic.',
            ],
        ];

        $medicineModels = [];
        foreach ($medicines as $med) {
            $medicineModels[] = MedicineCatalog::create($med);
        }

        // 3. Lab Test Catalog
        $labTests = [
            [
                'test_code' => 'CBC-001',
                'test_name' => 'Complete Blood Count (CBC) with Differential',
                'category' => 'Hematology',
                'standard_price' => 35.00,
                'discount_percentage' => 10.00,
                'specimen_type' => 'Whole Blood (EDTA)',
                'turnaround_hours' => 6,
            ],
            [
                'test_code' => 'LIPID-002',
                'test_name' => 'Comprehensive Lipid Panel (Total, HDL, LDL, Triglycerides)',
                'category' => 'Biochemistry',
                'standard_price' => 55.00,
                'discount_percentage' => 15.00,
                'specimen_type' => 'Serum (Fasting 12h)',
                'turnaround_hours' => 12,
            ],
            [
                'test_code' => 'HBA1C-003',
                'test_name' => 'Glycated Hemoglobin (HbA1c)',
                'category' => 'Biochemistry',
                'standard_price' => 45.00,
                'discount_percentage' => 10.00,
                'specimen_type' => 'Whole Blood (EDTA)',
                'turnaround_hours' => 12,
            ],
            [
                'test_code' => 'LFT-004',
                'test_name' => 'Liver Function Panel (ALT, AST, Bilirubin, Protein)',
                'category' => 'Biochemistry',
                'standard_price' => 65.00,
                'discount_percentage' => 5.00,
                'specimen_type' => 'Serum',
                'turnaround_hours' => 18,
            ],
            [
                'test_code' => 'CXR-006',
                'test_name' => 'Chest Radiography (Chest X-Ray PA View)',
                'category' => 'Radiology',
                'standard_price' => 80.00,
                'discount_percentage' => 10.00,
                'specimen_type' => 'Digital Radiograph',
                'turnaround_hours' => 2,
            ],
        ];

        $labTestModels = [];
        foreach ($labTests as $test) {
            $labTestModels[] = LabTestCatalog::create($test);
        }

        // 4. Primary Household Patient Account (Phone Authentication)
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

        // 5. Phase 1 Appointments with Daily Serials & Tokens
        // Appointment 1: Johnathan Miller (Serial #1) - New Patient Visit (Fee: $75)
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
            'status' => 'IN_PROGRESS', // In Doctor consultation room!
            'type' => 'CONSULTATION',
            'chief_complaint' => 'Hypertension follow-up and chronic fatigue.',
        ]);

        // Generate official Consultation invoice for App 1
        BillingEngine::generateConsultationInvoice($app1, 'CREDIT_CARD', 'CC-AUTH-8821', 'Paid at front desk reception.');

        // Appointment 2: Robert Sterling (Serial #2) - Walk-in New Visit (Fee: $75, Paid at reception -> CHECKED_IN)
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
            'status' => 'CHECKED_IN', // Waiting in queue (Next in Line)!
            'type' => 'WALK_IN',
            'chief_complaint' => 'Chest tightness on exertion and dizziness.',
        ]);
        BillingEngine::generateConsultationInvoice($app2, 'CASH', null, 'Cash paid at front desk.');

        // Appointment 3: Claire Miller (Serial #3) - Report Visit / Follow-up (Fee: $30, Unpaid -> Awaiting Cashier)
        $app3 = Appointment::create([
            'patient_id' => $patientSpouse->id,
            'provider_id' => $doctor1->id,
            'appointment_date' => now()->addHours(1),
            'visit_type' => 'REPORT_VISIT',
            'serial_number' => 3,
            'token_number' => 'TK-003',
            'fee_amount' => 30.00,
            'payment_status' => 'UNPAID',
            'status' => 'SCHEDULED', // Waiting to pay at front desk
            'type' => 'FOLLOW_UP',
            'chief_complaint' => 'Review of fasting lab test reports.',
        ]);

        // 6. Clinical Encounter & EHR for Johnathan Miller (Currently with Doctor)
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

        // Prescribe Medications
        $rx = Prescription::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor1->id,
            'notes' => 'Take with morning meals.',
            'status' => 'ACTIVE',
        ]);

        $lisinopril = $medicineModels[3];
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

        // Doctor Recommended Diagnostic Tests (Ready for Phase 4 Diagnostic Billing Counter!)
        $labOrder = LabOrder::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor1->id,
            'clinical_notes' => 'Annual lipid and glycemic control evaluation.',
            'priority' => 'ROUTINE',
            'status' => 'ORDERED',
        ]);

        // Test 1: Lipid Panel (UNBILLED - Patient can choose at diagnostic desk)
        $lipidTest = $labTestModels[1];
        LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $lipidTest->id,
            'standard_price' => $lipidTest->standard_price,
            'discount_percentage' => $lipidTest->discount_percentage,
            'discount_amount' => round($lipidTest->standard_price * 0.15, 2),
            'net_price' => round($lipidTest->standard_price - round($lipidTest->standard_price * 0.15, 2), 2),
            'billing_status' => 'UNBILLED',
            'result_status' => 'PENDING',
        ]);

        // Test 2: HbA1c (UNBILLED - Patient can choose at diagnostic desk)
        $hba1cTest = $labTestModels[2];
        LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $hba1cTest->id,
            'standard_price' => $hba1cTest->standard_price,
            'discount_percentage' => $hba1cTest->discount_percentage,
            'discount_amount' => round($hba1cTest->standard_price * 0.10, 2),
            'net_price' => round($hba1cTest->standard_price - round($hba1cTest->standard_price * 0.10, 2), 2),
            'billing_status' => 'UNBILLED',
            'result_status' => 'PENDING',
        ]);

        // Test 3: Chest X-Ray (UNBILLED)
        $cxrTest = $labTestModels[4];
        LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $cxrTest->id,
            'standard_price' => $cxrTest->standard_price,
            'discount_percentage' => $cxrTest->discount_percentage,
            'discount_amount' => round($cxrTest->standard_price * 0.10, 2),
            'net_price' => round($cxrTest->standard_price - round($cxrTest->standard_price * 0.10, 2), 2),
            'billing_status' => 'UNBILLED',
            'result_status' => 'PENDING',
        ]);

        // 7. Audit Trail Entries
        AuditLogger::log('INITIALIZE_HMS_SEED', 'System', $admin->id, ['workflow' => 'End-to-End HMS']);
    }
}
