<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabOrderItem;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class BillingEngine
{
    /**
     * Phase 2: Generate official Consultation Invoice upon arrival fee collection.
     * Updates payment status to PAID and pushes patient to active Doctor Queue.
     */
    public static function generateConsultationInvoice(
        Appointment $appointment,
        string $paymentMethod = 'CASH',
        ?string $reference = null,
        ?string $notes = null
    ): Invoice {
        return DB::transaction(function () use ($appointment, $paymentMethod, $reference, $notes) {
            $appointment->load(['patient', 'provider']);

            $invoice = Invoice::create([
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_type' => 'CONSULTATION',
                'subtotal' => $appointment->fee_amount,
                'discount_total' => 0.00,
                'tax_amount' => 0.00,
                'grand_total' => $appointment->fee_amount,
                'amount_paid' => $appointment->fee_amount,
                'balance_due' => 0.00,
                'status' => 'PAID',
                'payment_method' => $paymentMethod,
                'payment_reference' => $reference,
                'paid_at' => now(),
                'notes' => $notes ?: "Consultation fee collected for Serial #{$appointment->serial_number} ({$appointment->token_number}).",
            ]);

            $visitLabel = $appointment->visit_type === 'REPORT_VISIT' ? 'Report Visit / Follow-Up' : 'New Patient Visit / First Consultation';

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'CONSULTATION',
                'description' => "Doctor Visit Fee: {$visitLabel} - Dr. {$appointment->provider->name} ({$appointment->provider->specialization})",
                'reference_id' => $appointment->id,
                'quantity' => 1,
                'unit_price' => $appointment->fee_amount,
                'discount_amount' => 0.00,
                'total_price' => $appointment->fee_amount,
            ]);

            // Activate Queue: mark appointment as PAID and push to Doctor's active display queue (CHECKED_IN)
            $appointment->update([
                'payment_status' => 'PAID',
                'paid_at' => now(),
                'status' => 'CHECKED_IN',
            ]);

            AuditLogger::log('COLLECT_CONSULTATION_FEE', 'Appointment', $appointment->id, [
                'token_number' => $appointment->token_number,
                'serial_number' => $appointment->serial_number,
                'invoice_id' => $invoice->id,
                'amount_collected' => $appointment->fee_amount,
                'visit_type' => $appointment->visit_type,
            ]);

            return $invoice;
        });
    }

    /**
     * Phase 4: Diagnostic Billing & Flexible Test Selection.
     * Generates invoice for all or only specific doctor-prescribed tests chosen by the patient.
     */
    public static function generateDiagnosticInvoice(
        Patient $patient,
        array $selectedLabOrderItemIds,
        string $paymentMethod = 'CASH',
        ?string $reference = null,
        ?string $notes = null
    ): Invoice {
        return DB::transaction(function () use ($patient, $selectedLabOrderItemIds, $paymentMethod, $reference, $notes) {
            $labItems = LabOrderItem::with('test')
                ->whereIn('id', $selectedLabOrderItemIds)
                ->get();

            if ($labItems->isEmpty()) {
                throw new \InvalidArgumentException('No valid diagnostic tests selected for billing.');
            }

            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'invoice_type' => 'DIAGNOSTIC',
                'status' => 'UNPAID',
                'payment_method' => $paymentMethod,
                'payment_reference' => $reference,
                'notes' => $notes ?: 'Diagnostic laboratory and imaging tests billing.',
            ]);

            foreach ($labItems as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'LAB_TEST',
                    'description' => "Diagnostic Test: [{$item->test->test_code}] {$item->test->test_name} ({$item->test->category})",
                    'reference_id' => $item->id,
                    'quantity' => 1,
                    'unit_price' => $item->standard_price,
                    'discount_amount' => $item->discount_amount,
                    'total_price' => $item->net_price,
                ]);

                // Mark test item as BILLED and link invoice
                $item->update([
                    'billing_status' => 'BILLED',
                    'invoice_id' => $invoice->id,
                ]);
            }

            $invoice->load('items');
            $invoice->recalculate();

            // Immediate payment collection at cashier counter
            $invoice->amount_paid = $invoice->grand_total;
            $invoice->balance_due = 0.00;
            $invoice->status = 'PAID';
            $invoice->paid_at = now();
            $invoice->save();

            AuditLogger::log('GENERATE_DIAGNOSTIC_BILL', 'Invoice', $invoice->id, [
                'patient_id' => $patient->id,
                'tests_count' => count($selectedLabOrderItemIds),
                'grand_total' => $invoice->grand_total,
                'payment_method' => $paymentMethod,
            ]);

            return $invoice;
        });
    }

    /**
     * Generate or update an invoice for a completed clinical encounter (Rx & consultation).
     */
    public static function generateFromEncounter(Encounter $encounter): Invoice
    {
        return DB::transaction(function () use ($encounter) {
            $invoice = Invoice::firstOrNew([
                'encounter_id' => $encounter->id,
            ]);

            if (!$invoice->exists) {
                $invoice->patient_id = $encounter->patient_id;
                $invoice->appointment_id = $encounter->appointment_id;
                $invoice->invoice_type = 'COMBINED';
                $invoice->status = 'UNPAID';
                $invoice->amount_paid = 0.00;
                $invoice->save();
            }

            // Remove existing auto-generated items to avoid duplicates on re-bill
            $invoice->items()->delete();

            // Consultation Fee (if not already paid via appointment invoice)
            $consultationFee = (float) ($encounter->provider->consultation_fee ?? 75.00);
            $alreadyPaidAtFrontDesk = $encounter->appointment && $encounter->appointment->payment_status === 'PAID';

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'CONSULTATION',
                'description' => "Clinical Consultation - Dr. {$encounter->provider->name} ({$encounter->provider->specialization})",
                'reference_id' => $encounter->id,
                'quantity' => 1,
                'unit_price' => $consultationFee,
                'discount_amount' => 0.00,
                'total_price' => $consultationFee,
            ]);

            // Prescription Items
            $encounter->load(['prescriptions.items.medicine', 'labOrders.items.test']);
            foreach ($encounter->prescriptions as $prescription) {
                foreach ($prescription->items as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_type' => 'MEDICINE',
                        'description' => "Rx: {$item->medicine->brand_name} ({$item->medicine->strength}) - {$item->dosage}, {$item->frequency} x {$item->duration_days}d",
                        'reference_id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_amount' => $item->discount_amount,
                        'total_price' => $item->total_price,
                    ]);
                }
            }

            // Recalculate invoice totals
            $invoice->load('items');
            $invoice->recalculate();

            if ($alreadyPaidAtFrontDesk && (float) $invoice->amount_paid < $consultationFee) {
                $invoice->amount_paid = round((float) $invoice->amount_paid + $consultationFee, 2);
                $invoice->balance_due = max(0, round((float) $invoice->grand_total - $invoice->amount_paid, 2));
                if ($invoice->balance_due <= 0.001) {
                    $invoice->status = 'PAID';
                } else {
                    $invoice->status = 'PARTIALLY_PAID';
                }
                $invoice->save();
            }

            AuditLogger::log(
                'AUTO_GENERATE_INVOICE',
                'Invoice',
                $invoice->id,
                ['grand_total' => $invoice->grand_total, 'encounter_id' => $encounter->id]
            );

            return $invoice;
        });
    }
}
