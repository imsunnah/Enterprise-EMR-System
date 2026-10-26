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
          <p class="text-sm text-slate-500">Manage your patient appointments, initiate clinical encounters, and review past chartings.</p>
        </div>
      </div>

      <!-- Today's Queue -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-base font-bold text-slate-900">Today's Patient Queue</h3>
            <p class="text-xs text-slate-500">Patients waiting in exam rooms or checked in by triage</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
            {{ todayAppointments.length }} Patients
          </span>
        </div>

        <div v-if="todayAppointments.length === 0" class="py-12 text-center text-slate-400 text-sm">
          No patients currently waiting in your queue.
        </div>

        <div v-else class="divide-y divide-slate-100 mt-2">
          <div
            v-for="apt in todayAppointments"
            :key="apt.id"
            class="py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-slate-50/50 transition px-2 rounded-xl"
          >
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-base shrink-0 shadow-xs">
                {{ apt.patient.first_name[0] }}{{ apt.patient.last_name[0] }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-bold text-slate-900 text-base">{{ apt.patient.first_name }} {{ apt.patient.last_name }}</span>
                  <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-slate-100 text-slate-700">{{ apt.patient.mrn }}</span>
                  <span :class="[
                    apt.status === 'CHECKED_IN' ? 'bg-cyan-100 text-cyan-800' : 'bg-amber-100 text-amber-800',
                    'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase'
                  ]">
                    {{ apt.status }}
                  </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                  <span>{{ apt.patient.gender }} • {{ apt.patient.age }} yrs</span>
                  <span>Blood: <strong class="text-slate-700">{{ apt.patient.blood_group || 'N/A' }}</strong></span>
                  <span v-if="apt.patient.allergies" class="text-rose-600 font-medium">Allergy: {{ apt.patient.allergies }}</span>
                </div>
                <p v-if="apt.chief_complaint" class="text-xs text-slate-600 mt-1 italic">
                  Chief Complaint: "{{ apt.chief_complaint }}"
                </p>
              </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 self-end md:self-auto">
              <button
                @click="startEncounter(apt.id)"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-md shadow-teal-600/20 transition"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Open SOAP Charting &rarr;
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
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  todayAppointments: Array,
  recentEncounters: Array,
});

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
