<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Top Action Bar -->
      <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan-100 text-cyan-800">Front Desk & Triage</span>
            <span class="text-xs text-slate-500 font-medium">Outpatient Registration & Reception Desk</span>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Patient Check-In & Queue Management</h2>
          <p class="text-sm text-slate-500">Register walk-in patients, manage clinical arrival queues, and schedule appointments.</p>
        </div>

        <div class="flex items-center gap-3 self-start md:self-auto">
          <button
            @click="showScheduleModal = true"
            class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-xs transition"
          >
            + Schedule Appointment
          </button>
          <button
            @click="showWalkInModal = true"
            class="px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-md shadow-cyan-600/20 transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Register Walk-In Patient
          </button>
        </div>
      </div>

      <!-- Queue Status Filter Pills -->
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

      <!-- Queue Table -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-bold text-slate-900 text-sm">Active Reception Queue ({{ appointments.length }})</h3>
          <span class="text-xs text-slate-400">Sorted by arrival time</span>
        </div>

        <div v-if="appointments.length === 0" class="p-12 text-center text-slate-400 text-sm">
          No patients found in queue for this status.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Patient Information</th>
                <th class="py-3 px-4">MRN</th>
                <th class="py-3 px-4">Assigned Doctor</th>
                <th class="py-3 px-4">Type & Chief Complaint</th>
                <th class="py-3 px-4">Scheduled Time</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="apt in appointments" :key="apt.id" class="hover:bg-slate-50/60 transition">
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900 text-sm">{{ apt.patient?.first_name }} {{ apt.patient?.last_name }}</div>
                  <div class="text-slate-400 text-[11px]">{{ apt.patient?.gender }} • Phone: {{ apt.patient?.phone }}</div>
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-cyan-800">
                  {{ apt.patient?.mrn }}
                </td>
                <td class="py-3.5 px-4">
                  <span class="font-bold text-slate-800">{{ apt.provider?.name }}</span>
                  <span class="block text-slate-400 text-[11px]">{{ apt.provider?.specialization }}</span>
                </td>
                <td class="py-3.5 px-4 max-w-xs">
                  <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600 mr-1.5">
                    {{ apt.type }}
                  </span>
                  <span class="text-slate-600 truncate">{{ apt.chief_complaint || 'General Checkup' }}</span>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-700">
                  {{ formatDateTime(apt.appointment_date) }}
                </td>
                <td class="py-3.5 px-4">
                  <span :class="[
                    getStatusBadge(apt.status),
                    'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide'
                  ]">
                    {{ apt.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <button
                    v-if="apt.status === 'SCHEDULED'"
                    @click="checkInPatient(apt.id)"
                    class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition shadow-xs"
                  >
                    Check In Patient
                  </button>
                  <span v-else-if="apt.status === 'CHECKED_IN'" class="text-xs text-cyan-700 font-semibold">
                    In Waiting Area
                  </span>
                  <span v-else-if="apt.status === 'IN_PROGRESS'" class="text-xs text-amber-700 font-semibold">
                    In Exam Room
                  </span>
                  <span v-else-if="apt.status === 'COMPLETED'" class="text-xs text-emerald-700 font-semibold">
                    Consultation Finished
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal: Fast Walk-In Patient Registration -->
    <div v-if="showWalkInModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Walk-In Patient Fast Registration</h3>
            <p class="text-xs text-slate-500">Instantly generate MRN and assign to consultation queue</p>
          </div>
          <button @click="showWalkInModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitWalkIn" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">First Name *</label>
              <input v-model="walkInForm.first_name" required type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Last Name *</label>
              <input v-model="walkInForm.last_name" required type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Phone Number *</label>
              <input v-model="walkInForm.phone" required type="text" placeholder="+15550000000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Date of Birth *</label>
              <input v-model="walkInForm.date_of_birth" required type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Gender *</label>
              <select v-model="walkInForm.gender" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                <option value="MALE">Male</option>
                <option value="FEMALE">Female</option>
                <option value="OTHER">Other</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Blood Group</label>
              <input v-model="walkInForm.blood_group" placeholder="e.g. O+, A-" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Insurance Provider</label>
              <input v-model="walkInForm.insurance_provider" placeholder="e.g. BlueCross, Medicare" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
            </div>
          </div>

          <!-- Household Account Option -->
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="walkInForm.create_online_account" type="checkbox" class="w-4 h-4 text-cyan-600 rounded border-slate-300" />
              <span class="font-bold text-slate-800">Create / Link Online Household Account for Patient</span>
            </label>
            <p class="text-[11px] text-slate-500 mt-1 pl-6">
              When checked, enables patient to log in via phone number to view family records. Leave unchecked for standalone unregistered walk-ins.
            </p>
          </div>

          <!-- Immediate Doctor Assignment -->
          <div class="p-3 bg-cyan-50/60 rounded-xl border border-cyan-200 space-y-2">
            <span class="block font-bold text-cyan-900 uppercase tracking-wide text-[10px]">Direct Clinical Triage (Optional)</span>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[10px] text-slate-600">Assign Attending Physician</label>
                <select v-model="walkInForm.provider_id" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
                  <option value="">No immediate doctor (Queue later)</option>
                  <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                    {{ doc.name }} ({{ doc.specialization }})
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] text-slate-600">Chief Complaint / Triage Note</label>
                <input v-model="walkInForm.chief_complaint" placeholder="e.g. Acute chest pain, migraine..." class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white" />
              </div>
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showWalkInModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button :disabled="walkInForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-md shadow-cyan-600/20 transition">
              {{ walkInForm.processing ? 'Generating MRN...' : 'Register Patient & Generate MRN' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Schedule Appointment -->
    <div v-if="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">Schedule Patient Appointment</h3>
          <button @click="showScheduleModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitSchedule" class="mt-4 space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Select Patient *</label>
            <select v-model="scheduleForm.patient_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none">
              <option value="" disabled>Choose existing patient...</option>
              <option v-for="pt in recentPatients" :key="pt.id" :value="pt.id">
                {{ pt.first_name }} {{ pt.last_name }} ({{ pt.mrn }}) - {{ pt.phone }}
              </option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Attending Doctor *</label>
            <select v-model="scheduleForm.provider_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none">
              <option value="" disabled>Choose physician...</option>
              <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                {{ doc.name }} ({{ doc.specialization }})
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Appointment Date & Time *</label>
              <input v-model="scheduleForm.appointment_date" required type="datetime-local" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Appointment Type *</label>
              <select v-model="scheduleForm.type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                <option value="CONSULTATION">Consultation</option>
                <option value="ROUTINE_CHECKUP">Routine Checkup</option>
                <option value="FOLLOW_UP">Follow-Up</option>
                <option value="EMERGENCY">Emergency</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Chief Complaint</label>
            <input v-model="scheduleForm.chief_complaint" placeholder="Brief reason for appointment..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showScheduleModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button :disabled="scheduleForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-md shadow-cyan-600/20 transition">
              {{ scheduleForm.processing ? 'Booking...' : 'Book Appointment' }}
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
  appointments: Array,
  doctors: Array,
  recentPatients: Array,
  selectedStatus: String,
});

const showWalkInModal = ref(false);
const showScheduleModal = ref(false);

const statusFilters = [
  { id: 'ALL', label: 'All Queue' },
  { id: 'CHECKED_IN', label: 'Checked In' },
  { id: 'IN_PROGRESS', label: 'In Consultation' },
  { id: 'SCHEDULED', label: 'Scheduled' },
  { id: 'COMPLETED', label: 'Completed' },
];

const walkInForm = useForm({
  first_name: '',
  last_name: '',
  phone: '',
  date_of_birth: '',
  gender: 'MALE',
  blood_group: '',
  insurance_provider: '',
  create_online_account: true,
  provider_id: '',
  chief_complaint: '',
});

const scheduleForm = useForm({
  patient_id: '',
  provider_id: '',
  appointment_date: '',
  type: 'CONSULTATION',
  chief_complaint: '',
});

const filterStatus = (status) => {
  router.get('/clerk/queue', { status }, { preserveState: true });
};

const checkInPatient = (appointmentId) => {
  router.post(`/clerk/check-in/${appointmentId}`);
};

const submitWalkIn = () => {
  walkInForm.post('/clerk/register-walkin', {
    onSuccess: () => {
      showWalkInModal.value = false;
      walkInForm.reset();
    },
  });
};

const submitSchedule = () => {
  scheduleForm.post('/clerk/schedule', {
    onSuccess: () => {
      showScheduleModal.value = false;
      scheduleForm.reset();
    },
  });
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'CHECKED_IN': return 'bg-cyan-100 text-cyan-800';
    case 'IN_PROGRESS': return 'bg-amber-100 text-amber-800';
    case 'SCHEDULED': return 'bg-slate-100 text-slate-700';
    case 'COMPLETED': return 'bg-emerald-100 text-emerald-800';
    default: return 'bg-slate-100 text-slate-600';
  }
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
