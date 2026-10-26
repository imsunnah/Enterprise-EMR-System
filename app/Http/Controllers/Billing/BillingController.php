<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\LabOrderItem;
use App\Models\Patient;
use App\Services\AuditLogger;
use App\Services\BillingEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'ALL');
        $type = $request->query('type', 'ALL');
        $search = $request->query('search', '');

        $query = Invoice::with([
            'patient:id,mrn,first_name,last_name,phone,gender,insurance_provider',
            'appointment:id,token_number,serial_number,visit_type',
            'items',
        ])->latest();

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($type !== 'ALL') {
            $query->where('invoice_type', $type);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pQuery) use ($search) {
                      $pQuery->where('mrn', 'like', "%{$search}%")
                             ->orWhere('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20);

        // Revenue summary figures
        $totalRevenue = Invoice::where('status', 'PAID')->sum('amount_paid');
        $outstandingBalance = Invoice::whereIn('status', ['UNPAID', 'PARTIALLY_PAID'])->sum('balance_due');
        $unpaidCount = Invoice::where('status', 'UNPAID')->count();
        $consultationRevenue = Invoice::where('status', 'PAID')->where('invoice_type', 'CONSULTATION')->sum('amount_paid');
        $diagnosticRevenue = Invoice::where('status', 'PAID')->where('invoice_type', 'DIAGNOSTIC')->sum('amount_paid');

        AuditLogger::log('VIEW_BILLING_DESK', 'Invoice', '00000000-0000-0000-0000-000000000000');

        return Inertia::render('Billing/Index', [
            'invoices' => $invoices,
            'selectedStatus' => $status,
            'selectedType' => $type,
            'searchTerm' => $search,
            'summary' => [
                'total_revenue' => (float) $totalRevenue,
                'outstanding_balance' => (float) $outstandingBalance,
                'unpaid_count' => $unpaidCount,
                'consultation_revenue' => (float) $consultationRevenue,
                'diagnostic_revenue' => (float) $diagnosticRevenue,
            ],
        ]);
    }

    /**
     * Phase 4: Diagnostic Billing Counter - Flexible Test Itemization (Partial / Full).
     */
    public function diagnosticDesk(Request $request): Response
    {
        $search = $request->query('search', '');
        $selectedPatient = null;
        $recommendedTests = [];

        if (!empty($search)) {
            // Find by Patient MRN, Name, or Token Number
            $patient = Patient::where('mrn', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhereHas('appointments', function ($aQ) use ($search) {
                    $aQ->where('token_number', $search)->orWhere('serial_number', $search);
                })
                ->first();

            if ($patient) {
                $selectedPatient = $patient;

                // Fetch recommended lab tests with UNBILLED status
                $recommendedTests = LabOrderItem::with(['test', 'order.provider:id,name,specialization'])
                    ->whereHas('order', function ($oQ) use ($patient) {
                        $oQ->where('patient_id', $patient->id);
                    })
                    ->where('billing_status', 'UNBILLED')
                    ->latest()
                    ->get();
            }
        }

        $recentDiagnosticInvoices = Invoice::with(['patient:id,mrn,first_name,last_name', 'items'])
            ->where('invoice_type', 'DIAGNOSTIC')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Billing/DiagnosticDesk', [
            'searchTerm' => $search,
            'selectedPatient' => $selectedPatient,
            'recommendedTests' => $recommendedTests,
            'recentInvoices' => $recentDiagnosticInvoices,
        ]);
    }

    /**
     * Phase 4: Bill Selected Diagnostic Tests.
     */
    public function billDiagnostic(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|uuid|exists:patients,id',
            'selected_test_ids' => 'required|array|min:1',
            'selected_test_ids.*' => 'required|uuid|exists:lab_order_items,id',
            'payment_method' => 'required|in:CASH,CREDIT_CARD,DEBIT_CARD,INSURANCE,MOBILE_BANKING',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);

        $invoice = BillingEngine::generateDiagnosticInvoice(
            $patient,
            $validated['selected_test_ids'],
            $validated['payment_method'],
            $validated['payment_reference'] ?? null,
            $validated['notes'] ?? null
        );

        return redirect()->route('billing.invoice.show', $invoice->id)
            ->with('success', "Diagnostic bill {$invoice->invoice_number} generated for \${$invoice->grand_total}. Official receipt issued!");
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'patient:id,mrn,first_name,last_name,phone,address,gender,date_of_birth,insurance_provider,insurance_policy_number',
            'appointment:id,token_number,serial_number,visit_type,appointment_date',
            'items',
        ]);

        AuditLogger::log('VIEW_INVOICE_DETAILS', 'Invoice', $invoice->id, [
            'patient_mrn' => $invoice->patient->mrn,
            'invoice_type' => $invoice->invoice_type,
            'grand_total' => $invoice->grand_total,
        ]);

        return Inertia::render('Billing/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . ($invoice->balance_due + 0.01),
            'payment_method' => 'required|in:CASH,CREDIT_CARD,DEBIT_CARD,INSURANCE,MOBILE_BANKING',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $newPaid = (float) $invoice->amount_paid + (float) $validated['amount'];
        $invoice->amount_paid = round($newPaid, 2);
        $invoice->balance_due = max(0, round((float) $invoice->grand_total - $invoice->amount_paid, 2));
        $invoice->payment_method = $validated['payment_method'];
        $invoice->payment_reference = $validated['payment_reference'] ?? null;
        $invoice->notes = $validated['notes'] ?? $invoice->notes;

        if ($invoice->balance_due <= 0.001) {
            $invoice->status = 'PAID';
            $invoice->paid_at = now();
        } else {
            $invoice->status = 'PARTIALLY_PAID';
        }

        $invoice->save();

        AuditLogger::log('COLLECT_PAYMENT', 'Invoice', $invoice->id, [
            'amount_collected' => $validated['amount'],
            'method' => $validated['payment_method'],
            'remaining_balance' => $invoice->balance_due,
        ]);

        return back()->with('success', "Payment of \${$validated['amount']} recorded successfully for Invoice {$invoice->invoice_number}.");
    }
}
