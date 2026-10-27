<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientPortalController extends Controller
{
    public function dashboard(): Response
    {
        $accountId = session()->get('patient_account_id');
        if (!$accountId) {
            abort(403, 'Patient portal session expired. Please log in again.');
        }

        $account = PatientAccount::with('patients')->findOrFail($accountId);
        $familyMembers = $account->patients;

        $activePatientId = session()->get('active_patient_id', $familyMembers->first()?->id);
        $activePatient = $familyMembers->firstWhere('id', $activePatientId) ?: $familyMembers->first();

        $encounters = [];
        $prescriptions = [];
        $labOrders = [];
        $invoices = [];
        $appointments = [];

        if ($activePatient) {
            AuditLogger::log('VIEW_PHI_PATIENT_PORTAL', 'Patient', $activePatient->id);

            $encounters = $activePatient->encounters()
                ->with(['provider:id,name,specialization', 'diagnoses'])
                ->where('is_signed', true)
                ->get();

            $prescriptions = $activePatient->prescriptions()
                ->with(['provider:id,name', 'items.medicine'])
                ->get();

            $labOrders = $activePatient->labOrders()
                ->with(['items.test', 'provider:id,name'])
                ->get();

            $invoices = $activePatient->invoices()
                ->with('items')
                ->get();

            $appointments = $activePatient->appointments()
                ->with('provider:id,name,specialization,department')
                ->orderBy('appointment_date', 'asc')
                ->get();
        }

        $doctors = User::where('role', 'DOCTOR')
            ->where('is_active', true)
            ->select('id', 'name', 'specialization', 'department', 'consultation_fee', 'followup_fee')
            ->get();

        return Inertia::render('PatientPortal/Dashboard', [
            'account' => $account,
            'familyMembers' => $familyMembers,
            'activePatient' => $activePatient,
            'encounters' => $encounters,
            'prescriptions' => $prescriptions,
            'labOrders' => $labOrders,
            'invoices' => $invoices,
            'appointments' => $appointments,
            'doctors' => $doctors,
        ]);
    }

    public function switchProfile(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|uuid',
        ]);

        $accountId = session()->get('patient_account_id');
        $patient = Patient::where('id', $request->patient_id)
            ->where('account_id', $accountId)
            ->firstOrFail();

        session()->put('active_patient_id', $patient->id);

        AuditLogger::log('SWITCH_PATIENT_PROFILE', 'Patient', $patient->id);

        return redirect()->route('patient.dashboard')->with('success', "Switched profile to {$patient->full_name}");
    }

    public function addFamilyMember(Request $request)
    {
        $accountId = session()->get('patient_account_id');
        $account = PatientAccount::findOrFail($accountId);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:MALE,FEMALE,OTHER',
            'relationship_to_account_holder' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:150',
            'insurance_policy_number' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['account_id'] = $account->id;
        $validated['phone'] = $account->phone_number;
        $validated['relationship_to_account_holder'] = $validated['relationship_to_account_holder'] ?? 'PATIENT';

        $newPatient = Patient::create($validated);

        session()->put('active_patient_id', $newPatient->id);

        AuditLogger::log('CREATE_PATIENT', 'Patient', $newPatient->id, [
            'name' => $newPatient->full_name,
        ]);

        return redirect()->route('patient.dashboard')->with('success', "Patient {$newPatient->full_name} added successfully.");
    }

    public function updatePatient(Request $request, Patient $patient)
    {
        $accountId = session()->get('patient_account_id');
        if (!$accountId || $patient->account_id !== $accountId) {
            abort(403, 'Unauthorized to update this patient.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:MALE,FEMALE,OTHER',
            'blood_group' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:150',
            'insurance_policy_number' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:255',
        ]);

        $patient->update($validated);

        AuditLogger::log('UPDATE_PATIENT', 'Patient', $patient->id, [
            'name' => $patient->full_name,
        ]);

        return redirect()->route('patient.dashboard')->with('success', "Patient {$patient->full_name} details updated successfully.");
    }

    public function bookAppointment(Request $request)
    {
        $accountId = session()->get('patient_account_id');
        if (!$accountId) {
            abort(403, 'Session expired. Please log in again.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|uuid|exists:patients,id',
            'provider_id' => 'required|uuid|exists:users,id',
            'appointment_date' => 'required|date',
            'visit_type' => 'required|in:NEW_PATIENT,REPORT_VISIT',
            'type' => 'required|string',
            'chief_complaint' => 'nullable|string|max:500',
        ]);

        // Security check: ensure the patient profile belongs to the authenticated account
        $patient = Patient::where('id', $validated['patient_id'])
            ->where('account_id', $accountId)
            ->firstOrFail();

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'provider_id' => $validated['provider_id'],
            'appointment_date' => $validated['appointment_date'],
            'visit_type' => $validated['visit_type'],
            'status' => 'SCHEDULED',
            'payment_status' => 'UNPAID',
            'type' => $validated['type'],
            'chief_complaint' => $validated['chief_complaint'] ?? 'Online Self-Scheduled Consultation',
        ]);

        AuditLogger::log('BOOK_APPOINTMENT_PATIENT_PORTAL', 'Appointment', $appointment->id, [
            'patient_name' => $patient->full_name,
            'provider_id' => $validated['provider_id'],
            'serial_number' => $appointment->serial_number,
            'token_number' => $appointment->token_number,
            'visit_type' => $appointment->visit_type,
            'fee_amount' => $appointment->fee_amount,
        ]);

        return redirect()->route('patient.dashboard')->with('success', "Appointment scheduled! Your Daily Serial is #{$appointment->serial_number} ({$appointment->token_number}). Automated SMS & Email dispatched.");
    }
}
