<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\EncounterDiagnosis;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTestCatalog;
use App\Models\MedicineCatalog;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Services\AuditLogger;
use App\Services\BillingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EncounterController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $search = $request->query('search', '');
        $date = $request->query('date', now()->toDateString());

        $query = Appointment::with([
            'patient:id,mrn,first_name,last_name,date_of_birth,gender,phone,blood_group,allergies',
            'encounter:id,appointment_id,is_signed',
        ])
        ->where('provider_id', $user->id)
        ->whereDate('appointment_date', $date)
        ->orderBy('serial_number', 'asc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('token_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', $search)
                  ->orWhereHas('patient', function ($pQuery) use ($search) {
                      $pQuery->where('mrn', 'like', "%{$search}%")
                             ->orWhere('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $todayAppointments = $query->get();

        $recentEncounters = Encounter::with(['patient', 'diagnoses', 'appointment'])
            ->where('provider_id', $user->id)
            ->latest('encounter_date')
            ->take(20)
            ->get();

        return Inertia::render('Doctor/Index', [
            'todayAppointments' => $todayAppointments,
            'recentEncounters' => $recentEncounters,
            'searchTerm' => $search,
            'selectedDate' => $date,
        ]);
    }

    /**
     * Phase 3: Dedicated Waiting Room Live TV Display Screen outside/inside doctor's room.
     */
    public function waitingDisplay(Request $request): Response
    {
        $doctorId = $request->query('doctor_id', Auth::id());
        $doctor = \App\Models\User::where('role', 'DOCTOR')->find($doctorId) ?: Auth::user();

        $appointments = Appointment::with('patient:id,mrn,first_name,last_name,gender')
            ->where('provider_id', $doctor->id)
            ->whereDate('appointment_date', now()->toDateString())
            ->whereIn('status', ['CHECKED_IN', 'IN_PROGRESS', 'SCHEDULED', 'COMPLETED'])
            ->orderBy('serial_number', 'asc')
            ->get();

        $currentlyServing = $appointments->firstWhere('status', 'IN_PROGRESS');
        $nextInLine = $appointments->where('status', 'CHECKED_IN')->values();

        return Inertia::render('Doctor/WaitingRoomDisplay', [
            'doctor' => $doctor,
            'currentlyServing' => $currentlyServing,
            'nextInLine' => $nextInLine,
            'allQueue' => $appointments,
        ]);
    }

    public function startFromAppointment(Appointment $appointment)
    {
        $user = Auth::user();

        $encounter = Encounter::firstOrCreate([
            'appointment_id' => $appointment->id,
        ], [
            'patient_id' => $appointment->patient_id,
            'provider_id' => $user->id,
            'encounter_date' => now(),
            'subjective_notes' => $appointment->chief_complaint ? "Chief Complaint: {$appointment->chief_complaint}" : null,
            'is_signed' => false,
        ]);

        $appointment->update(['status' => 'IN_PROGRESS']);

        AuditLogger::log('START_CONSULTATION', 'Encounter', $encounter->id, [
            'serial_number' => $appointment->serial_number,
            'token_number' => $appointment->token_number,
            'patient_id' => $appointment->patient_id,
        ]);

        return redirect()->route('doctor.encounter.show', $encounter->id);
    }

    public function show(Encounter $encounter): Response
    {
        $user = Auth::user();
        if (in_array($user->role, ['BILLER', 'RECEPTIONIST'])) {
            abort(403, 'Billing & Reception personnel cannot view clinical SOAP records (PHI Protection).');
        }

        $encounter->load([
            'patient',
            'appointment',
            'diagnoses',
            'prescriptions.items.medicine',
            'labOrders.items.test',
            'invoice',
        ]);

        $patientHistory = Encounter::where('patient_id', $encounter->patient_id)
            ->where('id', '!=', $encounter->id)
            ->where('is_signed', true)
            ->with(['diagnoses', 'prescriptions.items.medicine'])
            ->latest('encounter_date')
            ->take(5)
            ->get();

        $medicines = MedicineCatalog::where('is_active', true)->orderBy('brand_name')->get();
        $labTests = LabTestCatalog::where('is_active', true)->orderBy('test_name')->get();

        AuditLogger::log('VIEW_PHI_ENCOUNTER', 'Encounter', $encounter->id, [
            'patient_id' => $encounter->patient_id,
            'doctor_id' => $user->id,
        ]);

        return Inertia::render('Doctor/Encounter', [
            'encounter' => $encounter,
            'patientHistory' => $patientHistory,
            'medicines' => $medicines,
            'labTests' => $labTests,
        ]);
    }

    public function saveDraft(Request $request, Encounter $encounter)
    {
        if ($encounter->is_signed) {
            return back()->with('error', 'Cannot edit a signed encounter.');
        }

        $validated = $request->validate([
            // Vitals
            'temperature_c' => 'nullable|numeric|between:30,45',
            'heart_rate_bpm' => 'nullable|integer|between:30,250',
            'systolic_bp' => 'nullable|integer|between:50,300',
            'diastolic_bp' => 'nullable|integer|between:30,200',
            'spo2_percentage' => 'nullable|numeric|between:50,100',
            'weight_kg' => 'nullable|numeric|between:1,400',
            'height_cm' => 'nullable|numeric|between:30,250',
            // SOAP notes
            'subjective_notes' => 'nullable|string',
            'objective_notes' => 'nullable|string',
            'assessment_notes' => 'nullable|string',
            'plan_notes' => 'nullable|string',
            // Diagnoses
            'diagnoses' => 'nullable|array',
            'diagnoses.*.icd10_code' => 'required|string|max:20',
            'diagnoses.*.description' => 'required|string|max:255',
            'diagnoses.*.type' => 'required|in:PRIMARY,SECONDARY,CHRONIC',
            // Prescriptions
            'prescriptions' => 'nullable|array',
            'prescriptions.*.medicine_id' => 'required|uuid|exists:medicine_catalog,id',
            'prescriptions.*.dosage' => 'required|string',
            'prescriptions.*.frequency' => 'required|string',
            'prescriptions.*.duration_days' => 'required|integer|min:1',
            'prescriptions.*.quantity' => 'required|integer|min:1',
            'prescriptions.*.instructions' => 'nullable|string',
            // Lab orders
            'lab_orders' => 'nullable|array',
            'lab_orders.*.lab_test_id' => 'required|uuid|exists:lab_test_catalog,id',
            'lab_orders.*.priority' => 'required|in:ROUTINE,URGENT,STAT',
        ]);

        DB::transaction(function () use ($encounter, $validated) {
            $encounter->fill([
                'temperature_c' => $validated['temperature_c'] ?? null,
                'heart_rate_bpm' => $validated['heart_rate_bpm'] ?? null,
                'systolic_bp' => $validated['systolic_bp'] ?? null,
                'diastolic_bp' => $validated['diastolic_bp'] ?? null,
                'spo2_percentage' => $validated['spo2_percentage'] ?? null,
                'weight_kg' => $validated['weight_kg'] ?? null,
                'height_cm' => $validated['height_cm'] ?? null,
                'subjective_notes' => $validated['subjective_notes'] ?? null,
                'objective_notes' => $validated['objective_notes'] ?? null,
                'assessment_notes' => $validated['assessment_notes'] ?? null,
                'plan_notes' => $validated['plan_notes'] ?? null,
            ]);
            $encounter->save();

            // Sync Diagnoses
            $encounter->diagnoses()->delete();
            if (!empty($validated['diagnoses'])) {
                foreach ($validated['diagnoses'] as $diag) {
                    $encounter->diagnoses()->create($diag);
                }
            }

            // Sync Prescriptions
            $prescription = $encounter->prescriptions()->first() ?: Prescription::create([
                'encounter_id' => $encounter->id,
                'patient_id' => $encounter->patient_id,
                'provider_id' => $encounter->provider_id,
                'status' => 'ACTIVE',
            ]);

            $prescription->items()->delete();
            if (!empty($validated['prescriptions'])) {
                foreach ($validated['prescriptions'] as $item) {
                    $med = MedicineCatalog::find($item['medicine_id']);
                    $unitPrice = (float) $med->unit_price;
                    $discountPct = (float) $med->discount_percentage;
                    $discountAmt = round(($unitPrice * ($discountPct / 100)) * (int) $item['quantity'], 2);
                    $totalPrice = round(($unitPrice * (int) $item['quantity']) - $discountAmt, 2);

                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $med->id,
                        'dosage' => $item['dosage'],
                        'frequency' => $item['frequency'],
                        'duration_days' => $item['duration_days'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'discount_percentage' => $discountPct,
                        'discount_amount' => $discountAmt,
                        'total_price' => $totalPrice,
                        'instructions' => $item['instructions'] ?? null,
                    ]);
                }
            }

            // Sync Lab Orders with UNBILLED status ready for Phase 4 Diagnostic Billing
            $labOrder = $encounter->labOrders()->first() ?: LabOrder::create([
                'encounter_id' => $encounter->id,
                'patient_id' => $encounter->patient_id,
                'provider_id' => $encounter->provider_id,
                'status' => 'ORDERED',
            ]);

            $labOrder->items()->delete();
            if (!empty($validated['lab_orders'])) {
                foreach ($validated['lab_orders'] as $lo) {
                    $test = LabTestCatalog::find($lo['lab_test_id']);
                    $price = (float) $test->standard_price;
                    $discountPct = (float) $test->discount_percentage;
                    $discountAmt = round($price * ($discountPct / 100), 2);
                    $netPrice = round($price - $discountAmt, 2);

                    LabOrderItem::create([
                        'lab_order_id' => $labOrder->id,
                        'lab_test_id' => $test->id,
                        'standard_price' => $price,
                        'discount_percentage' => $discountPct,
                        'discount_amount' => $discountAmt,
                        'net_price' => $netPrice,
                        'billing_status' => 'UNBILLED',
                        'result_status' => 'PENDING',
                    ]);
                }
            }

            AuditLogger::log('SAVE_DRAFT_ENCOUNTER', 'Encounter', $encounter->id, [
                'diagnoses_count' => count($validated['diagnoses'] ?? []),
                'prescriptions_count' => count($validated['prescriptions'] ?? []),
                'lab_orders_count' => count($validated['lab_orders'] ?? []),
            ]);
        });

        return back()->with('success', 'Encounter clinical draft saved.');
    }

    public function sign(Request $request, Encounter $encounter)
    {
        if ($encounter->is_signed) {
            return back()->with('info', 'Encounter is already signed.');
        }

        DB::transaction(function () use ($encounter) {
            $encounter->is_signed = true;
            $encounter->signed_at = now();
            $encounter->save();

            if ($encounter->appointment) {
                $encounter->appointment->update(['status' => 'COMPLETED']);
            }

            // Sync with pharmacy & combined billing
            $invoice = BillingEngine::generateFromEncounter($encounter);

            AuditLogger::log('SIGN_ENCOUNTER', 'Encounter', $encounter->id, [
                'signed_at' => $encounter->signed_at,
                'invoice_id' => $invoice->id,
                'invoice_total' => $invoice->grand_total,
            ]);
        });

        return redirect()->route('doctor.encounter.show', $encounter->id)
            ->with('success', 'Prescription & Encounter signed successfully! Synced with Diagnostic & Billing desks.');
    }
}
