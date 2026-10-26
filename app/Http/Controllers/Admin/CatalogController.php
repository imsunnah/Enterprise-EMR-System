<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LabTestCatalog;
use App\Models\MedicineCatalog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(): Response
    {
        $medicines = MedicineCatalog::latest()->get();
        $labTests = LabTestCatalog::latest()->get();
        $auditLogs = AuditLog::latest('created_at')->take(60)->get();
        $users = User::select('id', 'name', 'email', 'role', 'department', 'specialization', 'license_number', 'consultation_fee', 'followup_fee', 'is_active')->get();

        return Inertia::render('Admin/Catalogs', [
            'medicines' => $medicines,
            'labTests' => $labTests,
            'auditLogs' => $auditLogs,
            'users' => $users,
        ]);
    }

    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:200',
            'generic_name' => 'required|string|max:200',
            'dosage_form' => 'required|string|max:100',
            'strength' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['discount_percentage'] = $validated['discount_percentage'] ?? 0.00;
        $medicine = MedicineCatalog::create($validated);

        AuditLogger::log('CREATE_MEDICINE', 'MedicineCatalog', $medicine->id, $medicine->toArray());

        return back()->with('success', "Medicine {$medicine->brand_name} added to catalog.");
    }

    public function updateMedicine(Request $request, MedicineCatalog $medicine)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:200',
            'generic_name' => 'required|string|max:200',
            'dosage_form' => 'required|string|max:100',
            'strength' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $medicine->update($validated);

        AuditLogger::log('UPDATE_MEDICINE', 'MedicineCatalog', $medicine->id, $medicine->toArray());

        return back()->with('success', "Medicine {$medicine->brand_name} updated.");
    }

    public function storeLabTest(Request $request)
    {
        $validated = $request->validate([
            'test_code' => 'required|string|max:50|unique:lab_test_catalog,test_code',
            'test_name' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'standard_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'specimen_type' => 'nullable|string|max:100',
            'turnaround_hours' => 'nullable|integer|min:1',
        ]);

        $validated['discount_percentage'] = $validated['discount_percentage'] ?? 0.00;
        $test = LabTestCatalog::create($validated);

        AuditLogger::log('CREATE_LAB_TEST', 'LabTestCatalog', $test->id, $test->toArray());

        return back()->with('success', "Lab test {$test->test_name} added.");
    }

    public function updateLabTest(Request $request, LabTestCatalog $labTest)
    {
        $validated = $request->validate([
            'test_name' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'standard_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'is_active' => 'boolean',
        ]);

        $labTest->update($validated);

        AuditLogger::log('UPDATE_LAB_TEST', 'LabTestCatalog', $labTest->id, $labTest->toArray());

        return back()->with('success', "Lab test {$labTest->test_name} updated.");
    }

    /**
     * RBAC: Staff Management & Doctor Fee Configuration.
     */
    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:ADMIN,DOCTOR,NURSE,RECEPTIONIST,BILLER',
            'department' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:150',
            'license_number' => 'nullable|string|max:50',
            'consultation_fee' => 'nullable|numeric|min:0',
            'followup_fee' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department' => $validated['department'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'consultation_fee' => $validated['consultation_fee'] ?? 75.00,
            'followup_fee' => $validated['followup_fee'] ?? 30.00,
            'is_active' => true,
        ]);

        AuditLogger::log('CREATE_STAFF_USER', 'User', $user->id, [
            'name' => $user->name,
            'role' => $user->role,
            'consultation_fee' => $user->consultation_fee,
            'followup_fee' => $user->followup_fee,
        ]);

        return back()->with('success', "Staff member {$user->name} created with role {$user->role}.");
    }

    public function updateStaff(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'role' => 'required|in:ADMIN,DOCTOR,NURSE,RECEPTIONIST,BILLER',
            'department' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:150',
            'consultation_fee' => 'nullable|numeric|min:0',
            'followup_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        AuditLogger::log('UPDATE_STAFF_USER', 'User', $user->id, $validated);

        return back()->with('success', "Staff user {$user->name} configuration updated.");
    }
}
