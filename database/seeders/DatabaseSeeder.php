<?php

namespace Database\Seeders;

use App\Models\LabTestCatalog;
use App\Models\MedicineCatalog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed base operational data: Staff credentials, Medicine & Lab Test Catalogs.
     * Clean slate: Zero patients, appointments, encounters, or invoices.
     */
    public function run(): void
    {
        // 1. Staff Users & Roles (Password for all: secret123)
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

        foreach ($medicines as $med) {
            MedicineCatalog::create($med);
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

        foreach ($labTests as $test) {
            LabTestCatalog::create($test);
        }

        // 4. Initial Audit Log
        AuditLogger::log('INITIALIZE_HMS_SYSTEM', 'System', $admin->id, ['status' => 'Clean Slate Initialized']);
    }
}
