<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Doctor Workstation</span>
            <span class="text-xs text-slate-500 font-medium">Internal Medicine & Cardiology</span>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Clinical Consultation Desk</h2>
          <p class="text-sm text-slate-500">Manage daily serial patient queue, initiate clinical encounters, and review past chartings.</p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Launch Waiting Room Live Display -->
          <Link
            href="/doctor/display"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold shadow-md shadow-slate-900/10 transition"
          >
            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span>Live TV Waiting Display &rarr;</span>
          </Link>
        </div>
      </div>

      <!-- Today's Queue with Search & Filters -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-base font-bold text-slate-900">Today's Daily Serial Queue</h3>
            <p class="text-xs text-slate-500">Verified & checked-in patients waiting for consultation</p>
          </div>

          <!-- Real-Time Search & Filter (Phase 3.1) -->
          <div class="flex items-center gap-3 flex-wrap">
            <div class="relative w-72">
              <input
                v-model="searchQuery"
                @input="handleSearch"
                type="text"
                placeholder="Search serial #, token, MRN, name..."
                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
              />
              <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
              {{ todayAppointments.length }} Patients In Queue
            </span>
          </div>
        </div>

        <div v-if="todayAppointments.length === 0" class="py-12 text-center text-slate-400 text-sm">
          No patients found in your daily queue matching this filter.
        </div>

        <div v-else class="divide-y divide-slate-100 mt-2">
          <div
            v-for="apt in todayAppointments"
            :key="apt.id"
            class="py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-slate-50/70 transition px-3 rounded-xl border border-transparent hover:border-slate-200"
          >
            <div class="flex items-start gap-4">
              <!-- Prominent Daily Serial Badge -->
              <div class="flex flex-col items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-500 text-white font-mono font-black shrink-0 shadow-md shadow-teal-600/20">
                <span class="text-xs uppercase font-medium tracking-tight text-teal-100">SN</span>
                <span class="text-lg leading-none">#{{ apt.serial_number || '-' }}</span>
              </div>

              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-bold text-slate-900 text-base">
                    {{ apt.patient.first_name }} {{ apt.patient.last_name }}
                  </span>
                  <!-- Token Number Badge -->
                  <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                    {{ apt.token_number || ('TK-' + String(apt.serial_number).padStart(3, '0')) }}
                  </span>
                  <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-slate-100 text-slate-700">
                    {{ apt.patient.mrn }}
                  </span>

                  <!-- Visit Type Badge (New vs Report Visit) -->
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                      apt.visit_type === 'REPORT_VISIT'
                        ? 'bg-amber-100 text-amber-800 border border-amber-300'
                        : 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                    ]"
                  >
                    {{ apt.visit_type === 'REPORT_VISIT' ? 'Report / Follow-up' : 'First Visit' }}
                  </span>

                  <!-- Payment Status -->
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                      apt.payment_status === 'PAID'
                        ? 'bg-teal-100 text-teal-800'
                        : 'bg-rose-100 text-rose-800'
                    ]"
                  >
                    Fee: {{ apt.payment_status }} (${{ Number(apt.fee_amount || 0).toFixed(2) }})
                  </span>

                  <!-- Status -->
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                      apt.status === 'IN_PROGRESS'
                        ? 'bg-purple-100 text-purple-800 ring-1 ring-purple-400'
                        : apt.status === 'CHECKED_IN'
                        ? 'bg-cyan-100 text-cyan-800'
                        : 'bg-slate-100 text-slate-700'
                    ]"
                  >
                    {{ apt.status }}
                  </span>
                </div>

                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                  <span>{{ apt.patient.gender }} • {{ apt.patient.age || 'Adult' }} yrs</span>
                  <span>Blood: <strong class="text-slate-700">{{ apt.patient.blood_group || 'N/A' }}</strong></span>
                  <span v-if="apt.patient.allergies" class="text-rose-600 font-semibold">
                    Allergy: {{ apt.patient.allergies }}
                  </span>
                </div>
                <p v-if="apt.chief_complaint" class="text-xs text-slate-600 mt-1 italic">
                  Chief Complaint: "{{ apt.chief_complaint }}"
                </p>
              </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 self-end md:self-auto">
              <button
                @click="startEncounter(apt.id)"
                :class="[
                  'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white font-semibold text-xs shadow-md transition',
                  apt.status === 'IN_PROGRESS'
                    ? 'bg-purple-600 hover:bg-purple-700 shadow-purple-600/20'
                    : 'bg-teal-600 hover:bg-teal-700 shadow-teal-600/20'
                ]"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ apt.status === 'IN_PROGRESS' ? 'Resume SOAP Charting' : 'Call & Start Consultation' }} &rarr;</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Consultations -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Recent Signed Encounters</h3>
        <div class="divide-y divide-slate-100 mt-2">
          <div
            v-for="enc in recentEncounters"
            :key="enc.id"
            class="py-3 flex items-center justify-between text-xs"
          >
            <div>
              <Link :href="`/doctor/encounter/${enc.id}`" class="font-bold text-teal-700 hover:underline text-sm">
                {{ enc.patient?.first_name }} {{ enc.patient?.last_name }} ({{ enc.patient?.mrn }})
              </Link>
              <div class="text-slate-400 mt-0.5">
                {{ formatDate(enc.encounter_date) }} •
                <span v-if="enc.diagnoses?.length > 0" class="text-slate-600">
                  {{ enc.diagnoses.map(d => d.icd10_code).join(', ') }}
                </span>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <span :class="[
                enc.is_signed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800',
                'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase'
              ]">
                {{ enc.is_signed ? 'Signed & Billed' : 'Draft' }}
              </span>
              <Link
                :href="`/doctor/encounter/${enc.id}`"
                class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold transition"
              >
                View Chart
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  todayAppointments: Array,
  recentEncounters: Array,
  searchTerm: String,
  selectedDate: String,
});

const searchQuery = ref(props.searchTerm || '');
let searchTimer = null;

const handleSearch = () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    router.get('/doctor/encounters', { search: searchQuery.value }, {
      preserveState: true,
      replace: true,
    });
  }, 300);
};

const startEncounter = (appointmentId) => {
  router.post(`/doctor/appointment/${appointmentId}/start`);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
