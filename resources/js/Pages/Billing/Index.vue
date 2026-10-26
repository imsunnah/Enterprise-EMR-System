<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Billing Header -->
      <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Revenue Cycle Management (RCM)</span>
            <span class="text-xs text-slate-500 font-medium">Cashier Desk & Insurance Claims</span>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Billing & Invoicing Desk</h2>
          <p class="text-sm text-slate-500">Auto-populated invoices from completed doctor encounters, prescriptions, and diagnostic labs.</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap self-start md:self-auto">
          <Link
            href="/billing/diagnostics"
            class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
            <span>Diagnostic Billing Counter &rarr;</span>
          </Link>

          <!-- Strict PHI Isolation Badge -->
          <div class="p-2.5 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v2H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-1V6a4 4 0 00-4-4zm2 6V6a2 2 0 10-4 0v2h4z" clip-rule="evenodd" />
            </svg>
            <span class="text-[11px]">
              <strong>PHI Protected:</strong> SOAP notes isolated.
            </span>
          </div>
        </div>
      </div>

      <!-- Financial Summary Metric Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide block">Total Paid Revenue</span>
          <span class="text-2xl font-black text-emerald-600 font-mono mt-1 block">
            ${{ summary.total_revenue.toFixed(2) }}
          </span>
          <span class="text-[11px] text-slate-400 mt-1 block">Cleared payments & insurance payouts</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide block">Outstanding Balance</span>
          <span class="text-2xl font-black text-rose-600 font-mono mt-1 block">
            ${{ summary.outstanding_balance.toFixed(2) }}
          </span>
          <span class="text-[11px] text-slate-400 mt-1 block">Pending patient co-pays & invoices</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide block">Unpaid Accounts</span>
          <span class="text-2xl font-black text-amber-600 font-mono mt-1 block">
            {{ summary.unpaid_count }} invoices
          </span>
          <span class="text-[11px] text-slate-400 mt-1 block">Awaiting payment settlement</span>
        </div>
      </div>

      <!-- Status Filter Tabs -->
      <div class="flex items-center gap-2 pb-2 overflow-x-auto">
        <button
          v-for="st in statusFilters"
          :key="st.id"
          @click="filterStatus(st.id)"
          :class="[
            selectedStatus === st.id
              ? 'bg-slate-900 text-white font-semibold shadow-xs'
              : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200',
            'px-4 py-2 rounded-xl text-xs transition'
          ]"
        >
          {{ st.label }}
        </button>
      </div>

      <!-- Invoice Table -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-bold text-slate-900 text-sm">Patient Invoices ({{ invoices.data.length }})</h3>
          <span class="text-xs text-slate-400">Page {{ invoices.current_page }} of {{ invoices.last_page }}</span>
        </div>

        <div v-if="invoices.data.length === 0" class="p-12 text-center text-slate-400 text-sm">
          No invoices found for this status.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Invoice #</th>
                <th class="py-3 px-4">Patient / Household</th>
                <th class="py-3 px-4">Date Issued</th>
                <th class="py-3 px-4">Subtotal</th>
                <th class="py-3 px-4">Discounts</th>
                <th class="py-3 px-4">Grand Total</th>
                <th class="py-3 px-4">Amount Paid</th>
                <th class="py-3 px-4">Balance Due</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-50/60 transition">
                <td class="py-3.5 px-4 font-mono font-bold text-amber-800">
                  <Link :href="`/billing/invoice/${inv.id}`" class="hover:underline">
                    {{ inv.invoice_number }}
                  </Link>
                </td>
                <td class="py-3.5 px-4">
                  <span class="font-bold text-slate-900 block text-sm">{{ inv.patient?.first_name }} {{ inv.patient?.last_name }}</span>
                  <span class="text-slate-400 text-[11px] font-mono">{{ inv.patient?.mrn }} • {{ inv.patient?.insurance_provider || 'Self-pay' }}</span>
                </td>
                <td class="py-3.5 px-4 text-slate-600">
                  {{ formatDate(inv.created_at) }}
                </td>
                <td class="py-3.5 px-4 font-mono font-medium">
                  ${{ inv.subtotal }}
                </td>
                <td class="py-3.5 px-4 font-mono text-emerald-600">
                  -${{ inv.discount_total }}
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 text-sm">
                  ${{ inv.grand_total }}
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">
                  ${{ inv.amount_paid }}
                </td>
                <td class="py-3.5 px-4 font-mono font-bold">
                  <span :class="inv.balance_due > 0 ? 'text-rose-600' : 'text-slate-400'">
                    ${{ inv.balance_due }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <span :class="[
                    getStatusBadge(inv.status),
                    'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide'
                  ]">
                    {{ inv.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right space-x-2">
                  <button
                    v-if="inv.balance_due > 0"
                    @click="openPayModal(inv)"
                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition"
                  >
                    Collect Pay
                  </button>
                  <Link
                    :href="`/billing/invoice/${inv.id}`"
                    class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition"
                  >
                    View Slip
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal: Record Payment -->
    <div v-if="showPayModal && targetInvoice" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Collect Patient Payment</h3>
            <p class="text-xs text-slate-500 font-mono">{{ targetInvoice.invoice_number }} • {{ targetInvoice.patient?.full_name }}</p>
          </div>
          <button @click="showPayModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <div class="p-3 my-3 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center text-xs">
          <span>Remaining Balance Due:</span>
          <span class="font-mono font-bold text-rose-600 text-base">${{ targetInvoice.balance_due }}</span>
        </div>

        <form @submit.prevent="submitPayment" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Payment Amount ($) *</label>
            <input
              v-model.number="payForm.amount"
              required
              type="number"
              step="0.01"
              :max="targetInvoice.balance_due"
              class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Payment Method *</label>
            <select v-model="payForm.payment_method" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
              <option value="CASH">Cash</option>
              <option value="CREDIT_CARD">Credit Card</option>
              <option value="DEBIT_CARD">Debit Card</option>
              <option value="INSURANCE">Insurance Claim</option>
              <option value="MOBILE_BANKING">Mobile Banking / Electronic</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Payment Reference / Transaction ID</label>
            <input v-model="payForm.payment_reference" placeholder="e.g. TXN-94821 or Claim #" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Payment Notes</label>
            <input v-model="payForm.notes" placeholder="Co-pay cleared, receipt issued..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" />
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showPayModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs">Cancel</button>
            <button :disabled="payForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20">
              {{ payForm.processing ? 'Processing...' : 'Confirm Payment & Issue Receipt' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  invoices: Object,
  summary: Object,
  selectedStatus: String,
});

const showPayModal = ref(false);
const targetInvoice = ref(null);

const statusFilters = [
  { id: 'ALL', label: 'All Invoices' },
  { id: 'UNPAID', label: 'Unpaid' },
  { id: 'PARTIALLY_PAID', label: 'Partially Paid' },
  { id: 'PAID', label: 'Paid in Full' },
];

const payForm = useForm({
  amount: 0,
  payment_method: 'CASH',
  payment_reference: '',
  notes: '',
});

const filterStatus = (status) => {
  router.get('/billing', { status }, { preserveState: true });
};

const openPayModal = (inv) => {
  targetInvoice.value = inv;
  payForm.amount = parseFloat(inv.balance_due);
  showPayModal.value = true;
};

const submitPayment = () => {
  if (!targetInvoice.value) return;
  payForm.post(`/billing/invoice/${targetInvoice.value.id}/pay`, {
    onSuccess: () => {
      showPayModal.value = false;
      payForm.reset();
    },
  });
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'PAID': return 'bg-emerald-100 text-emerald-800';
    case 'PARTIALLY_PAID': return 'bg-amber-100 text-amber-800';
    case 'UNPAID': return 'bg-rose-100 text-rose-800';
    default: return 'bg-slate-100 text-slate-600';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};
</script>
