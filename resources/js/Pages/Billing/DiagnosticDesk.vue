<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
              Phase 4: Diagnostic Billing Desk
            </span>
            <span class="text-xs text-slate-500 font-medium">Outpatient Laboratory & Imaging Counter</span>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Flexible Test Itemization & Billing</h2>
          <p class="text-sm text-slate-500">
            Fetch doctor-prescribed diagnostic tests by Serial Token or Patient ID. Select partial or full tests chosen by the patient and issue official receipts.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            href="/billing"
            class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition"
          >
            &larr; All Invoices & Ledger
          </Link>
        </div>
      </div>

      <!-- Search Prescription / Patient Bar -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
        <div>
          <label class="block font-bold text-slate-700 uppercase tracking-wide text-xs mb-1.5">
            Fetch Doctor's Electronic Order (Enter Patient MRN, Serial #, or Token)
          </label>
          <div class="flex gap-3">
            <div class="relative flex-1">
              <input
                v-model="searchInput"
                @keyup.enter="performSearch"
                type="text"
                placeholder="e.g. MRN-88219, TK-001, Serial #1, or Patient Name..."
                class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
              <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <button
              @click="performSearch"
              class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5"
            >
              <span>Fetch Prescribed Tests</span>
            </button>
          </div>
        </div>

        <!-- Quick Hint Pill -->
        <div class="text-[11px] text-slate-500 flex items-center gap-2">
          <span class="font-semibold text-slate-700">Quick Test Lookups:</span>
          <button @click="searchWith('TK-001')" class="underline hover:text-indigo-600 font-mono">TK-001</button> &bull;
          <button @click="searchWith('John')" class="underline hover:text-indigo-600">John Doe</button> &bull;
          <button @click="searchWith('Emma')" class="underline hover:text-indigo-600">Emma Watson</button>
        </div>
      </div>

      <!-- Main Desk Workspace: Patient & Test Selection Grid -->
      <div v-if="selectedPatient" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Prescribed Tests Selection (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
          <!-- Patient Banner -->
          <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-lg">
                {{ selectedPatient.first_name[0] }}{{ selectedPatient.last_name[0] }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-bold text-slate-900">
                    {{ selectedPatient.first_name }} {{ selectedPatient.last_name }}
                  </h3>
                  <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-slate-100 text-slate-700">
                    {{ selectedPatient.mrn }}
                  </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                  Phone: {{ selectedPatient.phone }} &bull; Gender: {{ selectedPatient.gender }} &bull; Insurance: {{ selectedPatient.insurance_provider || 'Self-Pay' }}
                </p>
              </div>
            </div>

            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
              Prescription Linked
            </span>
          </div>

          <!-- Tests Itemization Card -->
          <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <div>
                <h4 class="font-bold text-slate-900 text-base">Doctor Recommended Diagnostic Tests</h4>
                <p class="text-xs text-slate-500">Select which tests the patient wishes to conduct at this counter today</p>
              </div>

              <!-- Quick Select All / None -->
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  @click="selectAll"
                  class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold underline"
                >
                  Select All
                </button>
                <span class="text-slate-300">|</span>
                <button
                  type="button"
                  @click="deselectAll"
                  class="text-xs text-slate-500 hover:text-slate-700 font-semibold underline"
                >
                  Deselect All
                </button>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="!recommendedTests || recommendedTests.length === 0" class="py-10 text-center text-slate-400 text-xs">
              <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              No unbilled tests found for this patient. All prescribed tests have been billed or none were ordered.
            </div>

            <!-- Test Item Checkboxes -->
            <div v-else class="space-y-3">
              <div
                v-for="item in recommendedTests"
                :key="item.id"
                @click="toggleTest(item.id)"
                :class="[
                  selectedTestIds.includes(item.id)
                    ? 'bg-indigo-50/70 border-indigo-400 ring-1 ring-indigo-400/30'
                    : 'bg-white border-slate-200 hover:bg-slate-50',
                  'p-4 rounded-xl border cursor-pointer transition flex items-center justify-between gap-4'
                ]"
              >
                <div class="flex items-center gap-3">
                  <input
                    type="checkbox"
                    :checked="selectedTestIds.includes(item.id)"
                    @click.stop="toggleTest(item.id)"
                    class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500"
                  />
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-slate-900 text-sm">
                        {{ item.test?.test_name || 'Diagnostic Test' }}
                      </span>
                      <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 text-slate-700">
                        {{ item.test?.test_code }}
                      </span>
                      <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                        {{ item.test?.category || 'LAB' }}
                      </span>
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">
                      Ordered by: <strong class="text-slate-700">{{ item.order?.provider?.name || 'Physician' }}</strong>
                      <span v-if="item.test?.turnaround_time_hours"> &bull; TAT: {{ item.test.turnaround_time_hours }} hrs</span>
                    </div>
                  </div>
                </div>

                <div class="text-right shrink-0">
                  <div class="text-base font-extrabold text-slate-900">
                    ${{ Number(item.test?.price || 0).toFixed(2) }}
                  </div>
                  <span
                    :class="[
                      'text-[10px] font-bold uppercase',
                      selectedTestIds.includes(item.id) ? 'text-indigo-600' : 'text-slate-400'
                    ]"
                  >
                    {{ selectedTestIds.includes(item.id) ? 'Selected For Billing' : 'Excluded' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Notice on Partial Billing -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-start gap-2.5">
              <svg class="w-4 h-4 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>
                <strong>Flexible Test Selection Policy:</strong> Patients are free to conduct only select tests today. Unselected tests will remain as unbilled in their file and can be billed at a later date or conducted at an outside facility.
              </span>
            </div>
          </div>
        </div>

        <!-- Right: Billing Calculation & Invoice Checkout (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
          <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-5 sticky top-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <h3 class="font-bold text-slate-900 text-base">Diagnostic Bill Summary</h3>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                {{ selectedTestIds.length }} Tests Selected
              </span>
            </div>

            <!-- Selected Tests Line Items -->
            <div class="space-y-2 text-xs">
              <div v-if="selectedTestObjects.length === 0" class="py-6 text-center text-slate-400">
                Please select at least one test from the left to compute bill.
              </div>

              <div
                v-for="st in selectedTestObjects"
                :key="st.id"
                class="flex items-center justify-between py-1.5 border-b border-slate-100"
              >
                <span class="text-slate-700 font-medium truncate max-w-[200px]">
                  {{ st.test?.test_name }}
                </span>
                <span class="font-mono font-bold text-slate-900">
                  ${{ Number(st.test?.price || 0).toFixed(2) }}
                </span>
              </div>

              <!-- Computation Totals -->
              <div class="pt-3 space-y-2 border-t border-slate-200">
                <div class="flex justify-between text-slate-500">
                  <span>Tests Subtotal:</span>
                  <span class="font-bold text-slate-800 font-mono">${{ computedSubtotal.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                  <span>Hospital Tax (0%):</span>
                  <span class="font-bold text-slate-800 font-mono">$0.00</span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-slate-200">
                  <span class="font-bold text-slate-900 text-sm">Grand Total Due:</span>
                  <span class="font-black text-2xl text-indigo-700 font-mono">
                    ${{ computedSubtotal.toFixed(2) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Payment Form -->
            <form @submit.prevent="submitDiagnosticBill" class="space-y-4 pt-2 border-t border-slate-100 text-xs">
              <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Payment Method *</label>
                <div class="grid grid-cols-3 gap-2">
                  <label
                    v-for="pm in ['CASH', 'CREDIT_CARD', 'MOBILE_BANKING']"
                    :key="pm"
                    :class="[
                      billingForm.payment_method === pm
                        ? 'bg-indigo-50 border-indigo-500 text-indigo-900 font-bold ring-2 ring-indigo-400/20'
                        : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50',
                      'p-2.5 rounded-xl border text-center cursor-pointer transition'
                    ]"
                  >
                    <input type="radio" v-model="billingForm.payment_method" :value="pm" class="sr-only" />
                    <span>{{ pm.replace('_', ' ') }}</span>
                  </label>
                </div>
              </div>

              <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Receipt / Slip Reference</label>
                <input
                  v-model="billingForm.payment_reference"
                  type="text"
                  placeholder="e.g. POS-LAB-9921"
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div>
                <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Clerk Notes</label>
                <input
                  v-model="billingForm.notes"
                  type="text"
                  placeholder="Patient requested partial lab testing..."
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <button
                :disabled="selectedTestIds.length === 0 || billingForm.processing"
                type="submit"
                class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>{{ billingForm.processing ? 'Generating Bill...' : `Generate Diagnostic Bill ($${computedSubtotal.toFixed(2)})` }}</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Recent Diagnostic Invoices Audit Table -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">
          Recent Diagnostic Invoices & Receipts
        </h3>

        <div v-if="recentInvoices.length === 0" class="py-8 text-center text-slate-400 text-xs">
          No diagnostic invoices issued yet today.
        </div>

        <div v-else class="divide-y divide-slate-100 mt-2 text-xs">
          <div
            v-for="inv in recentInvoices"
            :key="inv.id"
            class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div>
              <div class="flex items-center gap-2">
                <Link :href="`/billing/invoice/${inv.id}`" class="font-mono font-bold text-indigo-700 hover:underline">
                  {{ inv.invoice_number }}
                </Link>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-50 text-indigo-800">
                  {{ inv.invoice_type || 'DIAGNOSTIC' }}
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                  {{ inv.status }}
                </span>
              </div>
              <div class="text-slate-500 mt-1">
                Patient: <strong class="text-slate-800">{{ inv.patient?.first_name }} {{ inv.patient?.last_name }}</strong> ({{ inv.patient?.mrn }})
                &bull; {{ inv.items?.length || 0 }} test item(s)
              </div>
            </div>

            <div class="flex items-center gap-4">
              <div class="text-right">
                <div class="font-bold text-slate-900 text-sm font-mono">
                  ${{ Number(inv.grand_total).toFixed(2) }}
                </div>
                <div class="text-[10px] text-slate-400">
                  Paid: ${{ Number(inv.amount_paid).toFixed(2) }}
                </div>
              </div>

              <Link
                :href="`/billing/invoice/${inv.id}`"
                class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition"
              >
                View Bill &rarr;
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  searchTerm: String,
  selectedPatient: Object,
  recommendedTests: Array,
  recentInvoices: Array,
});

const searchInput = ref(props.searchTerm || '');
const selectedTestIds = ref(
  props.recommendedTests ? props.recommendedTests.map(t => t.id) : []
);

const billingForm = useForm({
  patient_id: props.selectedPatient?.id || '',
  selected_test_ids: [],
  payment_method: 'CASH',
  payment_reference: '',
  notes: 'Diagnostic Tests - Patient Selected Items',
});

const performSearch = () => {
  if (!searchInput.value.trim()) return;
  router.get('/billing/diagnostics', { search: searchInput.value }, {
    preserveState: true,
  });
};

const searchWith = (query) => {
  searchInput.value = query;
  performSearch();
};

const toggleTest = (id) => {
  const index = selectedTestIds.value.indexOf(id);
  if (index > -1) {
    selectedTestIds.value.splice(index, 1);
  } else {
    selectedTestIds.value.push(id);
  }
};

const selectAll = () => {
  if (props.recommendedTests) {
    selectedTestIds.value = props.recommendedTests.map(t => t.id);
  }
};

const deselectAll = () => {
  selectedTestIds.value = [];
};

const selectedTestObjects = computed(() => {
  if (!props.recommendedTests) return [];
  return props.recommendedTests.filter(t => selectedTestIds.value.includes(t.id));
});

const computedSubtotal = computed(() => {
  return selectedTestObjects.value.reduce((acc, item) => {
    return acc + Number(item.test?.price || 0);
  }, 0);
});

const submitDiagnosticBill = () => {
  if (!props.selectedPatient || selectedTestIds.value.length === 0) return;
  billingForm.patient_id = props.selectedPatient.id;
  billingForm.selected_test_ids = selectedTestIds.value;
  billingForm.post('/billing/diagnostics/bill');
};
</script>
