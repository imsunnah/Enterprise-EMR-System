<?php

namespace App\Http\Controllers\Clerk;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\BillingEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'ALL');
        $search = $request->query('search', '');
        $date = $request->query('date', now()->toDateString());

        $query = Appointment::with([
            'patient:id,mrn,first_name,last_name,date_of_birth,gender,phone,blood_group',
            'provider:id,name,specialization,department,consultation_fee,followup_fee',
            'encounter:id,appointment_id,is_signed',
            'invoice:id,invoice_number,grand_total,amount_paid,balance_due,status',
        ])
        ->whereDate('appointment_date', $date)
        ->orderBy('serial_number', 'asc');

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('token_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', $search)
                  ->orWhereHas('patient', function ($pQuery) use ($search) {
                      $pQuery->where('mrn', 'like', "%{$search}%")
                             ->orWhere('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $appointments = $query->get();

        $doctors = User::where('role', 'DOCTOR')
            ->where('is_active', true)
            ->select('id', 'name', 'specialization', 'department', 'consultation_fee', 'followup_fee')
            ->get();

        $recentPatients = Patient::latest()->take(30)->get(['id', 'mrn', 'first_name', 'last_name', 'phone', 'gender', 'date_of_birth']);

        AuditLogger::log('VIEW_QUEUE', 'Appointment', '00000000-0000-0000-0000-000000000000');

        return Inertia::render('Clerk/Queue', [
            'appointments' => $appointments,
            'doctors' => $doctors,
            'recentPatients' => $recentPatients,
            'selectedStatus' => $status,
            'selectedDate' => $date,
            'searchTerm' => $search,
        ]);
    }

    public function registerWalkIn(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:MALE,FEMALE,OTHER',
            'phone' => 'required|string|max:25',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:25',
            'insurance_provider' => 'nullable|string|max:150',
            'create_online_account' => 'boolean',
            'relationship_to_account_holder' => 'nullable|string',
            // Visit & Triage assignment
            'provider_id' => 'required|uuid|exists:users,id',
            'visit_type' => 'required|in:NEW_PATIENT,REPORT_VISIT',
            'chief_complaint' => 'nullable|string',
            'collect_fee_now' => 'boolean',
            'payment_method' => 'nullable|string',
            'payment_reference' => 'nullable|string',
        ]);

        $accountId = null;

        if (!empty($validated['create_online_account'])) {
            $account = PatientAccount::firstOrCreate(
                ['phone_number' => $validated['phone']],
                ['is_verified' => true]
            );
            $accountId = $account->id;
        }

        $patient = Patient::create([
            'account_id' => $accountId,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'],
            'blood_group' => $validated['blood_group'] ?? null,
            'address' => $validated['address'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'relationship_to_account_holder' => $validated['relationship_to_account_holder'] ?? 'SELF',
            'insurance_provider' => $validated['insurance_provider'] ?? null,
        ]);

        // Phase 1: Create appointment with daily serial and visit type
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'provider_id' => $validated['provider_id'],
            'appointment_date' => now(),
            'visit_type' => $validated['visit_type'],
            'status' => 'SCHEDULED',
            'type' => 'WALK_IN',
            'chief_complaint' => $validated['chief_complaint'] ?? 'Walk-in Visit',
        ]);

        // Phase 2: If fee collected now at cashier desk, generate invoice and activate queue
        if (!empty($validated['collect_fee_now'])) {
            BillingEngine::generateConsultationInvoice(
                $appointment,
                $validated['payment_method'] ?? 'CASH',
                $validated['payment_reference'] ?? null
            );
        }

        AuditLogger::log('REGISTER_WALK_IN_PATIENT', 'Patient', $patient->id, [
            'mrn' => $patient->mrn,
            'name' => $patient->full_name,
            'token_number' => $appointment->token_number,
            'serial_number' => $appointment->serial_number,
        ]);

        return redirect()->route('clerk.queue')->with('success', "Patient {$patient->full_name} registered with MRN: {$patient->mrn}. Serial #{$appointment->serial_number} ({$appointment->token_number}) assigned.");
    }

    public function collectFee(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:CASH,CREDIT_CARD,DEBIT_CARD,INSURANCE,MOBILE_BANKING',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $invoice = BillingEngine::generateConsultationInvoice(
            $appointment,
            $validated['payment_method'],
            $validated['payment_reference'] ?? null,
            $validated['notes'] ?? null
        );

        return back()->with('success', "Payment of \${$appointment->fee_amount} collected. Token {$appointment->token_number} is now activated in Doctor's Queue!");
    }

    public function checkIn(Appointment $appointment)
    {
        $appointment->update([
            'status' => 'CHECKED_IN',
        ]);

        AuditLogger::log('CHECK_IN_PATIENT', 'Appointment', $appointment->id, ['status' => 'CHECKED_IN']);

        return back()->with('success', "Patient checked in to provider's queue.");
    }

    public function scheduleAppointment(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|uuid|exists:patients,id',
            'provider_id' => 'required|uuid|exists:users,id',
            'appointment_date' => 'required|date',
            'visit_type' => 'required|in:NEW_PATIENT,REPORT_VISIT',
            'type' => 'required|string',
            'chief_complaint' => 'nullable|string',
        ]);

        $appointment = Appointment::create([
            ...$validated,
            'status' => 'SCHEDULED',
            'payment_status' => 'UNPAID',
        ]);

        AuditLogger::log('SCHEDULE_APPOINTMENT', 'Appointment', $appointment->id, [
            'token_number' => $appointment->token_number,
            'serial_number' => $appointment->serial_number,
            'visit_type' => $appointment->visit_type,
        ]);

        return back()->with('success', "Appointment scheduled! Token #{$appointment->serial_number} ({$appointment->token_number}) generated. Automated SMS & Email dispatched.");
    }
}
