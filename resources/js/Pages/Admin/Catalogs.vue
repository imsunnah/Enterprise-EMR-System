<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Admin Header -->
      <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">Administrator Console</span>
            <span class="text-xs text-slate-500 font-medium">Enterprise Master Catalogs & PHI Governance</span>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 mt-1">System Setup & Audit Trails</h2>
          <p class="text-sm text-slate-500">Configure pharmacy medicines, laboratory test pricing, and inspect immutable audit logs.</p>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto">
          <button
            v-if="activeTab === 'medicines'"
            @click="showAddMedModal = true"
            class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-600/20 transition flex items-center gap-1.5"
          >
            + Add Medicine to Catalog
          </button>
          <button
            v-if="activeTab === 'lab_tests'"
            @click="showAddLabModal = true"
            class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md shadow-purple-600/20 transition flex items-center gap-1.5"
          >
            + Add Lab Test to Catalog
          </button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-2 pb-2 border-b border-slate-200 overflow-x-auto">
        <button
          v-for="t in tabs"
          :key="t.id"
          @click="activeTab = t.id"
          :class="[
            activeTab === t.id
              ? 'bg-slate-900 text-white font-semibold shadow-xs'
              : 'text-slate-600 hover:bg-slate-200/60',
            'px-4 py-2 rounded-xl text-xs transition flex items-center gap-2'
          ]"
        >
          <span>{{ t.label }}</span>
          <span :class="[
            activeTab === t.id ? 'bg-purple-500 text-white' : 'bg-slate-200 text-slate-700',
            'px-1.5 py-0.2 rounded-full text-[10px] font-bold'
          ]">{{ t.count }}</span>
        </button>
      </div>

      <!-- TAB 1: MEDICINE CATALOG -->
      <div v-if="activeTab === 'medicines'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-bold text-slate-900 text-sm">Medicine Setup & Inventory Rates</h3>
          <span class="text-xs text-slate-400">Total: {{ medicines.length }} items</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Brand & Generic Name</th>
                <th class="py-3 px-4">Form & Strength</th>
                <th class="py-3 px-4">Unit Price</th>
                <th class="py-3 px-4">Default Discount</th>
                <th class="py-3 px-4">Active Stock</th>
                <th class="py-3 px-4">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="med in medicines" :key="med.id" class="hover:bg-slate-50/60 transition">
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900 text-sm">{{ med.brand_name }}</div>
                  <div class="text-slate-400 text-[11px]">{{ med.generic_name }}</div>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-700">
                  {{ med.dosage_form }} • {{ med.strength }}
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 text-sm">
                  ${{ med.unit_price }}
                </td>
                <td class="py-3.5 px-4">
                  <span v-if="med.discount_percentage > 0" class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                    {{ med.discount_percentage }}%
                  </span>
                  <span v-else class="text-slate-400">0%</span>
                </td>
                <td class="py-3.5 px-4 font-mono font-bold">
                  <span :class="med.stock_quantity < 50 ? 'text-rose-600' : 'text-slate-800'">
                    {{ med.stock_quantity }} units
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <span :class="[
                    med.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600',
                    'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase'
                  ]">
                    {{ med.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 2: LAB TEST CATALOG -->
      <div v-if="activeTab === 'lab_tests'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-bold text-slate-900 text-sm">Diagnostic Test Catalog & Turnaround Engine</h3>
          <span class="text-xs text-slate-400">Total: {{ labTests.length }} tests</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Test Code</th>
                <th class="py-3 px-4">Diagnostic Name</th>
                <th class="py-3 px-4">Category</th>
                <th class="py-3 px-4">Standard Rate</th>
                <th class="py-3 px-4">Discount</th>
                <th class="py-3 px-4">Specimen Type</th>
                <th class="py-3 px-4">Turnaround</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="test in labTests" :key="test.id" class="hover:bg-slate-50/60 transition">
                <td class="py-3.5 px-4 font-mono font-bold text-purple-800">
                  {{ test.test_code }}
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-900 text-sm">
                  {{ test.test_name }}
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-600">
                  {{ test.category }}
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 text-sm">
                  ${{ test.standard_price }}
                </td>
                <td class="py-3.5 px-4">
                  <span v-if="test.discount_percentage > 0" class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                    {{ test.discount_percentage }}%
                  </span>
                  <span v-else class="text-slate-400">0%</span>
                </td>
                <td class="py-3.5 px-4 text-slate-500">
                  {{ test.specimen_type || 'N/A' }}
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-700">
                  {{ test.turnaround_hours }} hours
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 3: IMMUTABLE AUDIT LOGS -->
      <div v-if="activeTab === 'audit_logs'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-900 text-sm">Immutable PHI Compliance & Security Audit Trail</h3>
            <p class="text-xs text-slate-400">Chronological append-only records of all access and mutations</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
            Compliant
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs font-mono">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Timestamp (UTC)</th>
                <th class="py-3 px-4">Operator / User</th>
                <th class="py-3 px-4">Role</th>
                <th class="py-3 px-4">Action</th>
                <th class="py-3 px-4">Entity</th>
                <th class="py-3 px-4">IP Address</th>
                <th class="py-3 px-4">Payload Changes</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="log in auditLogs" :key="log.id" class="hover:bg-slate-50/70 transition">
                <td class="py-3 px-4 text-slate-500 font-normal">
                  {{ formatDateTime(log.created_at) }}
                </td>
                <td class="py-3 px-4 font-bold text-slate-900 font-sans">
                  {{ log.user_name || 'System' }}
                </td>
                <td class="py-3 px-4">
                  <span :class="[
                    log.user_role === 'DOCTOR' ? 'text-emerald-700 bg-emerald-50' :
                    log.user_role === 'ADMIN' ? 'text-purple-700 bg-purple-50' :
                    log.user_role === 'BILLER' ? 'text-amber-700 bg-amber-50' : 'text-slate-700 bg-slate-100',
                    'px-2 py-0.5 rounded text-[10px] font-bold'
                  ]">
                    {{ log.user_role }}
                  </span>
                </td>
                <td class="py-3 px-4 font-bold text-teal-800">
                  {{ log.action }}
                </td>
                <td class="py-3 px-4 text-slate-600">
                  {{ log.entity_name }}
                </td>
                <td class="py-3 px-4 text-slate-500">
                  {{ log.ip_address || '127.0.0.1' }}
                </td>
                <td class="py-3 px-4 max-w-xs truncate text-[11px] text-slate-500">
                  {{ log.changes ? JSON.stringify(log.changes) : '—' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 4: STAFF DIRECTORY -->
      <div v-if="activeTab === 'users'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-bold text-slate-900 text-sm">Authorized Staff & Clinical Roles</h3>
          <span class="text-xs text-slate-400">{{ users.length }} staff users</span>
        </div>

        <div class="divide-y divide-slate-100">
          <div v-for="u in users" :key="u.id" class="p-4 flex items-center justify-between hover:bg-slate-50 text-xs">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-bold flex items-center justify-center text-sm">
                {{ u.name[0] }}
              </div>
              <div>
                <span class="font-bold text-slate-900 text-sm block">{{ u.name }}</span>
                <span class="text-slate-400">{{ u.email }} • {{ u.specialization || 'General Staff' }}</span>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                {{ u.role }}
              </span>
              <span class="text-emerald-600 font-semibold text-xs">Active</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Add Medicine -->
    <div v-if="showAddMedModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">Add Medicine to Catalog</h3>
          <button @click="showAddMedModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitMedicine" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Brand Name *</label>
              <input v-model="medForm.brand_name" required placeholder="e.g. Lipitor" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Generic Name *</label>
              <input v-model="medForm.generic_name" required placeholder="e.g. Atorvastatin" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Dosage Form *</label>
              <input v-model="medForm.dosage_form" required placeholder="e.g. Tablet, Syrup, Injection" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Strength *</label>
              <input v-model="medForm.strength" required placeholder="e.g. 500mg, 10mg/ml" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Unit Price ($) *</label>
              <input v-model.number="medForm.unit_price" required type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Discount (%)</label>
              <input v-model.number="medForm.discount_percentage" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Stock Qty *</label>
              <input v-model.number="medForm.stock_quantity" required type="number" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showAddMedModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs">Cancel</button>
            <button :disabled="medForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20">
              Save Medicine
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Add Lab Test -->
    <div v-if="showAddLabModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">Add Diagnostic Lab Test</h3>
          <button @click="showAddLabModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitLabTest" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Test Code *</label>
              <input v-model="labForm.test_code" required placeholder="e.g. CBC-001, GLUC-002" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm uppercase font-mono" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Test Name *</label>
              <input v-model="labForm.test_name" required placeholder="e.g. Complete Blood Count" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Category *</label>
              <select v-model="labForm.category" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                <option value="Hematology">Hematology</option>
                <option value="Biochemistry">Biochemistry</option>
                <option value="Microbiology">Microbiology</option>
                <option value="Radiology">Radiology</option>
                <option value="Pathology">Pathology</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Specimen Type</label>
              <input v-model="labForm.specimen_type" placeholder="e.g. Serum, Whole Blood, Urine" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Standard Rate ($) *</label>
              <input v-model.number="labForm.standard_price" required type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Discount (%)</label>
              <input v-model.number="labForm.discount_percentage" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Turnaround (Hrs)</label>
              <input v-model.number="labForm.turnaround_hours" type="number" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showAddLabModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs">Cancel</button>
            <button :disabled="labForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20">
              Save Lab Test
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  medicines: Array,
  labTests: Array,
  auditLogs: Array,
  users: Array,
});

const activeTab = ref('medicines');
const showAddMedModal = ref(false);
const showAddLabModal = ref(false);

const tabs = computed(() => [
  { id: 'medicines', label: 'Medicine Catalog', count: props.medicines?.length || 0 },
  { id: 'lab_tests', label: 'Lab Test Catalog', count: props.labTests?.length || 0 },
  { id: 'audit_logs', label: 'Audit Compliance Logs', count: props.auditLogs?.length || 0 },
  { id: 'users', label: 'Staff Roster & Roles', count: props.users?.length || 0 },
]);

const medForm = useForm({
  brand_name: '',
  generic_name: '',
  dosage_form: 'Tablet',
  strength: '',
  unit_price: 10.00,
  discount_percentage: 0.00,
  stock_quantity: 100,
});

const labForm = useForm({
  test_code: '',
  test_name: '',
  category: 'Hematology',
  standard_price: 40.00,
  discount_percentage: 0.00,
  specimen_type: 'Blood',
  turnaround_hours: 12,
});

const submitMedicine = () => {
  medForm.post('/admin/catalogs/medicines', {
    onSuccess: () => {
      showAddMedModal.value = false;
      medForm.reset();
    },
  });
};

const submitLabTest = () => {
  labForm.post('/admin/catalogs/lab-tests', {
    onSuccess: () => {
      showAddLabModal.value = false;
      labForm.reset();
    },
  });
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};
</script>
