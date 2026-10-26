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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Staff Users
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@emr.local',
            'password' => Hash::make('secret123'),
            'role' => 'ADMIN',
            'department' => 'Administration',
            'phone' => '+15550000001',
            'is_active' => true,
        ]);

        $doctor = User::create([
            'name' => 'Dr. Sarah Jenkins, MD',
            'email' => 'doctor@emr.local',
            'password' => Hash::make('secret123'),
            'role' => 'DOCTOR',
            'department' => 'Internal Medicine',
            'specialization' => 'Cardiologist & General Physician',
            'license_number' => 'MD-749219',
            'consultation_fee' => 75.00,
            'phone' => '+15550000002',
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
            'department' => 'Revenue Cycle & Accounts',
            'specialization' => 'Billing & Claims Specialist',
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
                'description' => 'ACE inhibitor for management of essential hypertension and heart failure.',
            ],
            [
                'brand_name' => 'Prilosec',
                'generic_name' => 'Omeprazole',
                'dosage_form' => 'Delayed-release Capsule',
                'strength' => '40mg',
                'unit_price' => 14.50,
                'discount_percentage' => 0.00,
                'stock_quantity' => 150,
                'description' => 'Proton-pump inhibitor for gastroesophageal reflux disease and ulcers.',
            ],
            [
                'brand_name' => 'Zithromax',
                'generic_name' => 'Azithromycin',
                'dosage_form' => 'Tablet',
                'strength' => '250mg',
                'unit_price' => 22.00,
                'discount_percentage' => 12.00,
                'stock_quantity' => 95,
                'description' => 'Macrolide antibiotic for respiratory tract and soft tissue infections.',
            ],
            [
                'brand_name' => 'Tylenol Extra Strength',
                'generic_name' => 'Paracetamol (Acetaminophen)',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'unit_price' => 5.00,
                'discount_percentage' => 0.00,
                'stock_quantity' => 1200,
                'description' => 'Analgesic and antipyretic for mild-to-moderate pain and pyrexia.',
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
                'test_name' => 'Liver Function Panel (ALT, AST, ALP, Bilirubin, Protein)',
                'category' => 'Biochemistry',
                'standard_price' => 65.00,
                'discount_percentage' => 5.00,
                'specimen_type' => 'Serum',
                'turnaround_hours' => 18,
            ],
            [
                'test_code' => 'URINE-005',
                'test_name' => 'Routine Urinalysis with Microscopic Examination',
                'category' => 'Pathology',
                'standard_price' => 25.00,
                'discount_percentage' => 0.00,
                'specimen_type' => 'Mid-stream Urine',
                'turnaround_hours' => 4,
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

        // Family Profile 1: Self
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

        // Family Profile 2: Spouse
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
            'insurance_policy_number' => 'BCBS-9842104-B',
            'allergies' => 'None reported',
        ]);

        // Family Profile 3: Child
        $patientChild = Patient::create([
            'account_id' => $account->id,
            'mrn' => 'MRN-2026-10494',
            'first_name' => 'Lucas',
            'last_name' => 'Miller',
            'date_of_birth' => '2017-08-03',
            'gender' => 'MALE',
            'blood_group' => 'O+',
            'phone' => '+15551234567',
            'address' => '742 Evergreen Terrace, Springfield',
            'relationship_to_account_holder' => 'CHILD',
            'emergency_contact_name' => 'Johnathan Miller',
            'emergency_contact_phone' => '+15551234567',
            'allergies' => 'Peanuts',
        ]);

        // Walk-in Patient (No Online Account)
        $patientWalkIn = Patient::create([
            'account_id' => null, // Walk-in
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
            'insurance_policy_number' => 'MED-5541-X8',
            'allergies' => 'Sulfa drugs',
        ]);

        // 5. Appointments
        $app1 = Appointment::create([
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor->id,
            'appointment_date' => now()->subDays(2),
            'status' => 'COMPLETED',
            'type' => 'ROUTINE_CHECKUP',
            'chief_complaint' => 'Annual hypertension follow-up and intermittent fatigue.',
            'notes' => 'Patient fasting for lipid panel.',
        ]);

        $app2 = Appointment::create([
            'patient_id' => $patientWalkIn->id,
            'provider_id' => $doctor->id,
            'appointment_date' => now()->addHours(2),
            'status' => 'CHECKED_IN',
            'type' => 'WALK_IN',
            'chief_complaint' => 'Mild chest discomfort and palpitations after exertion.',
            'notes' => 'Priority triage vitals taken at front desk.',
        ]);

        $app3 = Appointment::create([
            'patient_id' => $patientChild->id,
            'provider_id' => $doctor->id,
            'appointment_date' => now()->addDays(3),
            'status' => 'SCHEDULED',
            'type' => 'PEDIATRIC_CHECKUP',
            'chief_complaint' => 'Routine 9-year pediatric wellness exam and vaccination check.',
        ]);

        // 6. Clinical Encounter for Johnathan Miller
        $encounter = Encounter::create([
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor->id,
            'appointment_id' => $app1->id,
            'encounter_date' => now()->subDays(2),
            // Vitals
            'temperature_c' => 36.8,
            'heart_rate_bpm' => 74,
            'systolic_bp' => 134,
            'diastolic_bp' => 86,
            'spo2_percentage' => 98.5,
            'weight_kg' => 84.5,
            'height_cm' => 178.0,
            'bmi' => 26.7,
            // Structured SOAP Notes
            'subjective_notes' => "Patient presents for 6-month hypertension review. Reports adhering to diet and mild exercise. Experiences afternoon fatigue. Denies chest pain, orthopnea, or shortness of breath. No visual disturbances.",
            'objective_notes' => "General: Well-nourished, alert male in no acute distress.\nCardiovascular: Regular rate and rhythm, S1/S2 audible, no murmurs or gallops. Peripheral pulses intact bilaterally.\nRespiratory: Clear to auscultation bilaterally, no wheezes or rales.\nAbdomen: Soft, non-tender, non-distended, normoactive bowel sounds.\nNeurological: Cranial nerves II-XII grossly intact.",
            'assessment_notes' => "1. Essential (primary) hypertension (ICD-10: I10) - Suboptimally controlled at 134/86.\n2. Type 2 diabetes mellitus without complications (ICD-10: E11.9) - Stable on oral hypoglycemics.\n3. Pure hypercholesterolemia (ICD-10: E78.0) - Follow-up lipid panel ordered.",
            'plan_notes' => "1. Continue Lisinopril 10mg once daily in morning.\n2. Refill Metformin 850mg twice daily with meals.\n3. Add Atorvastatin 20mg once daily at bedtime.\n4. Ordered Fasting Comprehensive Lipid Panel and HbA1c.\n5. Follow-up consultation in 6 weeks with home blood pressure logs.",
            'is_signed' => true,
            'signed_at' => now()->subDays(2),
        ]);

        // Diagnoses (ICD-10)
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

        EncounterDiagnosis::create([
            'encounter_id' => $encounter->id,
            'icd10_code' => 'E78.0',
            'description' => 'Pure hypercholesterolemia',
            'type' => 'CHRONIC',
        ]);

        // Catalog-Linked E-Prescription
        $rx = Prescription::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor->id,
            'notes' => 'Take with water. Report any muscle aches or unexpected dizziness.',
            'status' => 'ACTIVE',
        ]);

        // Prescription Item 1: Lisinopril
        $lisinopril = $medicineModels[3]; // Lisinopril 10mg
        $rxItem1 = PrescriptionItem::create([
            'prescription_id' => $rx->id,
            'medicine_id' => $lisinopril->id,
            'dosage' => '1 tablet',
            'frequency' => 'Once daily (morning)',
            'duration_days' => 30,
            'quantity' => 30,
            'unit_price' => $lisinopril->unit_price,
            'discount_percentage' => $lisinopril->discount_percentage,
            'discount_amount' => round(($lisinopril->unit_price * 0.10) * 1, 2),
            'total_price' => round(($lisinopril->unit_price * 1) - round(($lisinopril->unit_price * 0.10), 2), 2),
            'instructions' => 'Take early morning with a glass of water.',
        ]);

        // Prescription Item 2: Metformin
        $metformin = $medicineModels[1]; // Metformin 850mg
        $rxItem2 = PrescriptionItem::create([
            'prescription_id' => $rx->id,
            'medicine_id' => $metformin->id,
            'dosage' => '1 tablet',
            'frequency' => 'Twice daily',
            'duration_days' => 30,
            'quantity' => 60,
            'unit_price' => $metformin->unit_price,
            'discount_percentage' => $metformin->discount_percentage,
            'discount_amount' => round(($metformin->unit_price * 0.15) * 1, 2),
            'total_price' => round(($metformin->unit_price * 1) - round(($metformin->unit_price * 0.15), 2), 2),
            'instructions' => 'Take with morning and evening meals.',
        ]);

        // Lab Orders for Encounter
        $labOrder = LabOrder::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $patientSelf->id,
            'provider_id' => $doctor->id,
            'clinical_notes' => 'Fasting status confirmed. Annual metabolic evaluation.',
            'priority' => 'ROUTINE',
            'status' => 'COMPLETED',
        ]);

        $lipidTest = $labTestModels[1];
        LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $lipidTest->id,
            'standard_price' => $lipidTest->standard_price,
            'discount_percentage' => $lipidTest->discount_percentage,
            'discount_amount' => round($lipidTest->standard_price * 0.15, 2),
            'net_price' => round($lipidTest->standard_price - round($lipidTest->standard_price * 0.15, 2), 2),
            'result_value' => 'Total: 215 mg/dL | HDL: 44 mg/dL | LDL: 138 mg/dL | Triglycerides: 165 mg/dL',
            'reference_range' => 'Total < 200 mg/dL, LDL < 100 mg/dL',
            'result_status' => 'ABNORMAL',
            'result_notes' => 'Mild hyperlipidemia noted. Initiated Atorvastatin therapy.',
        ]);

        $hba1cTest = $labTestModels[2];
        LabOrderItem::create([
            'lab_order_id' => $labOrder->id,
            'lab_test_id' => $hba1cTest->id,
            'standard_price' => $hba1cTest->standard_price,
            'discount_percentage' => $hba1cTest->discount_percentage,
            'discount_amount' => round($hba1cTest->standard_price * 0.10, 2),
            'net_price' => round($hba1cTest->standard_price - round($hba1cTest->standard_price * 0.10, 2), 2),
            'result_value' => '6.4%',
            'reference_range' => '< 5.7% (Normal), 5.7 - 6.4% (Prediabetes), >= 6.5% (Diabetes)',
            'result_status' => 'NORMAL',
            'result_notes' => 'Glycemic control maintained within target range.',
        ]);

        // 7. Auto-Generated Revenue Cycle Invoice
        $invoice = Invoice::create([
            'invoice_number' => 'INV-2026-10048',
            'patient_id' => $patientSelf->id,
            'encounter_id' => $encounter->id,
            'subtotal' => 170.50,
            'discount_total' => 14.55,
            'tax_amount' => 0.00,
            'grand_total' => 155.95,
            'amount_paid' => 155.95,
            'balance_due' => 0.00,
            'status' => 'PAID',
            'payment_method' => 'INSURANCE',
            'payment_reference' => 'BCBS-CLM-849102',
            'paid_at' => now()->subDays(2),
            'notes' => 'Co-pay cleared by Blue Cross HealthNet authorization.',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'CONSULTATION',
            'description' => "Clinical Consultation - Dr. Sarah Jenkins, MD (Cardiologist & General Physician)",
            'reference_id' => $encounter->id,
            'quantity' => 1,
            'unit_price' => 75.00,
            'discount_amount' => 0.00,
            'total_price' => 75.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'MEDICINE',
            'description' => "Rx: Zestril (10mg) - 1 tablet, Once daily x 30d",
            'reference_id' => $rxItem1->id,
            'quantity' => 1,
            'unit_price' => 15.00,
            'discount_amount' => 1.50,
            'total_price' => 13.50,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'MEDICINE',
            'description' => "Rx: Glucophage (850mg) - 1 tablet, Twice daily x 30d",
            'reference_id' => $rxItem2->id,
            'quantity' => 1,
            'unit_price' => 12.00,
            'discount_amount' => 1.80,
            'total_price' => 10.20,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'LAB_TEST',
            'description' => "Lab Order: [LIPID-002] Comprehensive Lipid Panel (Biochemistry)",
            'reference_id' => $lipidTest->id,
            'quantity' => 1,
            'unit_price' => 55.00,
            'discount_amount' => 8.25,
            'total_price' => 46.75,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'LAB_TEST',
            'description' => "Lab Order: [HBA1C-003] Glycated Hemoglobin (HbA1c) (Biochemistry)",
            'reference_id' => $hba1cTest->id,
            'quantity' => 1,
            'unit_price' => 45.00,
            'discount_amount' => 4.50,
            'total_price' => 40.50,
        ]);

        // 8. Immutable Audit Trail Entries
        AuditLogger::log('INITIALIZE_EMR_SEED', 'System', $admin->id, ['environment' => 'development']);
        AuditLogger::log('VIEW_PHI_PATIENT', 'Patient', $patientSelf->id, ['mrn' => $patientSelf->mrn]);
        AuditLogger::log('SIGN_ENCOUNTER', 'Encounter', $encounter->id, ['signed_by' => $doctor->name]);
    }
}
