<template>
  <AuthenticatedLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Action Bar -->
      <div class="flex items-center justify-between no-print">
        <Link href="/billing" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
          &larr; Back to Billing Desk
        </Link>
        <div class="flex items-center gap-2">
          <button
            @click="printInvoice"
            class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold shadow-xs transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Billing Statement
          </button>
        </div>
      </div>

      <!-- Formal Clinical Billing Statement Card -->
      <div class="bg-white rounded-2xl p-8 sm:p-12 shadow-sm border border-slate-200 printable-area space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b border-slate-200 pb-8">
          <div>
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-teal-600 text-white font-black flex items-center justify-center text-base">
                +
              </div>
              <h1 class="text-xl font-black text-slate-900 tracking-tight">Synapse Health Hospital Network</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">100 Healthcare Parkway, Medical Plaza Suite 400</p>
            <p class="text-xs text-slate-500">Accounts & Billing Desk: (555) 019-2831</p>
          </div>

          <div class="text-left sm:text-right">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Itemized Billing Statement</span>
            <span class="text-2xl font-black font-mono text-slate-900 block mt-0.5">{{ invoice.invoice_number }}</span>
            <div class="mt-2 flex items-center gap-1.5 justify-start sm:justify-end flex-wrap">
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                {{ invoice.invoice_type || 'STANDARD' }}
              </span>
              <span v-if="invoice.appointment?.token_number" class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                Token: {{ invoice.appointment.token_number }} (#{{ invoice.appointment.serial_number }})
              </span>
              <span :class="[
                invoice.status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800',
                'px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider'
              ]">
                {{ invoice.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Bill To / Patient Information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-5 rounded-xl border border-slate-100 text-xs">
          <div>
            <span class="font-bold text-slate-400 uppercase tracking-wide text-[10px] block mb-1">Patient Demographics</span>
            <h4 class="font-extrabold text-slate-900 text-sm">{{ invoice.patient?.full_name }}</h4>
            <p class="text-slate-600 mt-0.5">MRN: <strong class="font-mono text-cyan-800">{{ invoice.patient?.mrn }}</strong></p>
            <p class="text-slate-600">DOB: {{ invoice.patient?.date_of_birth }} ({{ invoice.patient?.gender }})</p>
            <p class="text-slate-600">Phone: {{ invoice.patient?.phone }}</p>
            <p v-if="invoice.patient?.address" class="text-slate-500 mt-0.5">{{ invoice.patient?.address }}</p>
          </div>

          <div>
            <span class="font-bold text-slate-400 uppercase tracking-wide text-[10px] block mb-1">Payer & Claim Details</span>
            <p class="text-slate-700">Insurance Carrier: <strong>{{ invoice.patient?.insurance_provider || 'Self-Pay / Direct Patient Account' }}</strong></p>
            <p v-if="invoice.patient?.insurance_policy_number" class="text-slate-600">Policy / ID: <span class="font-mono font-medium">{{ invoice.patient?.insurance_policy_number }}</span></p>
            <p class="text-slate-600 mt-1">Date of Service: {{ formatDateTime(invoice.created_at) }}</p>
            <p v-if="invoice.payment_method" class="text-slate-600">Settlement Method: <strong>{{ invoice.payment_method }}</strong></p>
          </div>
        </div>

        <!-- Itemized Charges Table -->
        <div>
          <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wide pb-2 border-b border-slate-200 mb-3">
            Itemized Encounter Services & Pharmacy Breakdown
          </h3>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                  <th class="py-2.5 px-3">Service Type</th>
                  <th class="py-2.5 px-3">Description</th>
                  <th class="py-2.5 px-3 text-center">Qty</th>
                  <th class="py-2.5 px-3 text-right">Standard Rate</th>
                  <th class="py-2.5 px-3 text-right">Discount</th>
                  <th class="py-2.5 px-3 text-right">Line Net Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="it in invoice.items" :key="it.id">
                  <td class="py-3 px-3">
                    <span :class="[
                      it.item_type === 'CONSULTATION' ? 'bg-teal-50 text-teal-800' :
                      it.item_type === 'MEDICINE' ? 'bg-emerald-50 text-emerald-800' :
                      it.item_type === 'LAB_TEST' ? 'bg-sky-50 text-sky-800' : 'bg-slate-100 text-slate-700',
                      'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide'
                    ]">
                      {{ it.item_type }}
                    </span>
                  </td>
                  <td class="py-3 px-3 font-medium text-slate-900">
                    {{ it.description }}
                  </td>
                  <td class="py-3 px-3 text-center font-bold">
                    {{ it.quantity }}
                  </td>
                  <td class="py-3 px-3 text-right font-mono">
                    ${{ it.unit_price }}
                  </td>
                  <td class="py-3 px-3 text-right font-mono text-emerald-600">
                    {{ it.discount_amount > 0 ? `-$${it.discount_amount}` : '$0.00' }}
                  </td>
                  <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                    ${{ it.total_price }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Totals & Balances -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-t border-slate-200 pt-6">
          <div class="text-xs text-slate-400 max-w-sm">
            <p class="font-semibold text-slate-600 uppercase text-[10px] tracking-wide mb-1">Compliance & PHI Isolation Notice</p>
            <p>
              In strict compliance with PHI data scoping guidelines, this financial billing invoice records billable services, medication line items, and diagnostic charges without exposing private clinician SOAP encounter notes.
            </p>
          </div>

          <div class="w-full sm:w-72 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600">
              <span>Gross Subtotal:</span>
              <span class="font-mono font-medium">${{ invoice.subtotal }}</span>
            </div>
            <div class="flex justify-between text-emerald-700 font-medium">
              <span>Catalog Discounts Applied:</span>
              <span class="font-mono">-${{ invoice.discount_total }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
              <span>Tax (0% Medical Exemption):</span>
              <span class="font-mono">$0.00</span>
            </div>
            <div class="flex justify-between text-base font-extrabold text-slate-900 border-t border-slate-200 pt-2">
              <span>Grand Total:</span>
              <span class="font-mono">${{ invoice.grand_total }}</span>
            </div>
            <div class="flex justify-between text-slate-700 font-semibold">
              <span>Amount Paid:</span>
              <span class="font-mono text-emerald-600">${{ invoice.amount_paid }}</span>
            </div>
            <div class="flex justify-between text-base font-black border-t border-slate-200 pt-2">
              <span class="text-slate-900">Balance Due:</span>
              <span :class="invoice.balance_due > 0 ? 'text-rose-600' : 'text-slate-400'" class="font-mono">
                ${{ invoice.balance_due }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  invoice: Object,
});

const printInvoice = () => {
  window.print();
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<style>
@media print {
  .no-print {
    display: none !important;
  }
  aside {
    display: none !important;
  }
  header {
    display: none !important;
  }
  main {
    padding: 0 !important;
    background: white !important;
  }
  .printable-area {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
  }
}
</style>
