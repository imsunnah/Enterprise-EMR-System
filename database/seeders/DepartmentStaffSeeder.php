<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentStaffSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Administrator
            [
                'email' => 'admin@emr.local',
                'name' => 'System Administrator',
                'role' => 'ADMIN',
                'department' => 'Hospital Governance',
                'specialization' => 'Clinical Executive & Compliance',
                'phone' => '+15550000001',
                'is_active' => true,
            ],

            // 1. Medicine Department
            [
                'email' => 'doctor@emr.local',
                'name' => 'Dr. Sarah Jenkins, MD',
                'role' => 'DOCTOR',
                'department' => 'Medicine',
                'specialization' => 'Internal Medicine & General Physician',
                'license_number' => 'MD-749219',
                'consultation_fee' => 75.00,
                'followup_fee' => 30.00,
                'phone' => '+15550000002',
                'is_active' => true,
            ],
            [
                'email' => 'chen@emr.local',
                'name' => 'Dr. Robert Chen, MD',
                'role' => 'DOCTOR',
                'department' => 'Medicine',
                'specialization' => 'General Physician & Diabetologist',
                'license_number' => 'MD-881231',
                'consultation_fee' => 70.00,
                'followup_fee' => 30.00,
                'phone' => '+15550000011',
                'is_active' => true,
            ],

            // 2. Neurology (Neuro) Department
            [
                'email' => 'hayes@emr.local',
                'name' => 'Dr. David Hayes, MD',
                'role' => 'DOCTOR',
                'department' => 'Neurology',
                'specialization' => 'Senior Consultant Neurologist',
                'license_number' => 'MD-991204',
                'consultation_fee' => 90.00,
                'followup_fee' => 40.00,
                'phone' => '+15550000005',
                'is_active' => true,
            ],
            [
                'email' => 'rostova@emr.local',
                'name' => 'Dr. Elena Rostova, MD',
                'role' => 'DOCTOR',
                'department' => 'Neurology',
                'specialization' => 'Neuro-Specialist & Stroke Physician',
                'license_number' => 'MD-662319',
                'consultation_fee' => 85.00,
                'followup_fee' => 35.00,
                'phone' => '+15550000012',
                'is_active' => true,
            ],

            // 3. Orthopedics & Arthrology Department
            [
                'email' => 'brody@emr.local',
                'name' => 'Dr. Marcus Brody, MD',
                'role' => 'DOCTOR',
                'department' => 'Arthrology & Orthopedics',
                'specialization' => 'Consultant Orthopedic & Arthrology Surgeon',
                'license_number' => 'MD-443912',
                'consultation_fee' => 85.00,
                'followup_fee' => 35.00,
                'phone' => '+15550000013',
                'is_active' => true,
            ],
            [
                'email' => 'avance@emr.local',
                'name' => 'Dr. Arthur Vance, MD',
                'role' => 'DOCTOR',
                'department' => 'Arthrology & Orthopedics',
                'specialization' => 'Joint Arthrology & Spine Specialist',
                'license_number' => 'MD-552190',
                'consultation_fee' => 80.00,
                'followup_fee' => 30.00,
                'phone' => '+15550000014',
                'is_active' => true,
            ],

            // 4. Cardiology Department
            [
                'email' => 'patel@emr.local',
                'name' => 'Dr. Liam Patel, MD',
                'role' => 'DOCTOR',
                'department' => 'Cardiology',
                'specialization' => 'Interventional Cardiologist',
                'license_number' => 'MD-331902',
                'consultation_fee' => 95.00,
                'followup_fee' => 45.00,
                'phone' => '+15550000015',
                'is_active' => true,
            ],

            // 5. Receptionists
            [
                'email' => 'receptionist@emr.local',
                'name' => 'Sarah Connor',
                'role' => 'RECEPTIONIST',
                'department' => 'Front Desk & Reception',
                'specialization' => 'Senior Outpatient Cashier',
                'phone' => '+15550000006',
                'is_active' => true,
            ],

            // 6. Nurses (Separated by Clinical Ward / Department)
            [
                'email' => 'nurse@emr.local',
                'name' => 'Emily Clark, RN',
                'role' => 'NURSE',
                'department' => 'Triage & Emergency',
                'specialization' => 'Senior Triage Nurse',
                'license_number' => 'RN-382910',
                'phone' => '+15550000003',
                'is_active' => true,
            ],
            [
                'email' => 'rivera@emr.local',
                'name' => 'Jessica Rivera, RN',
                'role' => 'NURSE',
                'department' => 'Neurology Ward',
                'specialization' => 'Neuro ICU Staff Nurse',
                'license_number' => 'RN-442109',
                'phone' => '+15550000016',
                'is_active' => true,
            ],
            [
                'email' => 'kim@emr.local',
                'name' => 'David Kim, RN',
                'role' => 'NURSE',
                'department' => 'Arthrology & Orthopedics Ward',
                'specialization' => 'Surgical Arthrology Nurse',
                'license_number' => 'RN-551980',
                'phone' => '+15550000017',
                'is_active' => true,
            ],
            [
                'email' => 'santos@emr.local',
                'name' => 'Maria Santos, RN',
                'role' => 'NURSE',
                'department' => 'Medicine Ward',
                'specialization' => 'Inpatient Medicine Care Nurse',
                'license_number' => 'RN-667201',
                'phone' => '+15550000018',
                'is_active' => true,
            ],

            // 7. Biller
            [
                'email' => 'biller@emr.local',
                'name' => 'Michael Vance',
                'role' => 'BILLER',
                'department' => 'Revenue Cycle & Diagnostic Counter',
                'specialization' => 'Diagnostic Billing Specialist',
                'phone' => '+15550000004',
                'is_active' => true,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => Hash::make('secret123')])
            );
        }
    }
}
