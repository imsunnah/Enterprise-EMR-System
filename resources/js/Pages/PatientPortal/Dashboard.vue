<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Household Header & Profile Switcher Bar -->
      <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800">Household Account</span>
              <span class="text-xs font-mono text-slate-500">{{ account.phone_number }}</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Family Medical Records</h2>
            <p class="text-sm text-slate-500">Switch profiles below to review clinical records, prescriptions, and lab tests for your household.</p>
          </div>

          <div class="flex items-center gap-2.5 self-start md:self-auto">
            <button
              @click="openBookModal"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold shadow-md shadow-teal-600/20 transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Book Appointment
            </button>

            <button
              @click="showAddMemberModal = true"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold shadow-md shadow-sky-600/20 transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add Family Member
            </button>
          </div>
        </div>

        <!-- Household Members Carousel / Cards -->
        <div class="mt-6">
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Household Profiles</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div
              v-for="member in familyMembers"
              :key="member.id"
              @click="switchProfile(member.id)"
              :class="[
                activePatient?.id === member.id
                  ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/50 shadow-sm'
                  : 'border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50/80',
                'p-4 rounded-xl border cursor-pointer transition flex items-start gap-3 relative'
              ]"
            >
              <!-- Avatar with relationship icon -->
              <div :class="[
                activePatient?.id === member.id ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-600',
                'w-11 h-11 rounded-full flex items-center justify-center font-bold text-base shrink-0 shadow-xs'
              ]">
                {{ member.first_name[0] }}{{ member.last_name[0] }}
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="font-bold text-slate-900 text-sm truncate">{{ member.first_name }} {{ member.last_name }}</span>
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-600">
                    {{ member.relationship_to_account_holder }}
                  </span>
                  <span class="text-xs text-slate-400">{{ member.age }} yrs • {{ member.gender }}</span>
                </div>
                <p class="text-[11px] font-mono text-slate-500 mt-1 truncate">{{ member.mrn }}</p>
              </div>

              <span v-if="activePatient?.id === member.id" class="w-2.5 h-2.5 rounded-full bg-sky-500 absolute top-3 right-3"></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Profile Banner -->
      <div v-if="activePatient" class="bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white rounded-2xl p-6 shadow-md">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-sky-500/20 border border-sky-400/40 flex items-center justify-center text-sky-300 font-extrabold text-2xl">
              {{ activePatient.first_name[0] }}{{ activePatient.last_name[0] }}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-xl font-extrabold">{{ activePatient.first_name }} {{ activePatient.last_name }}</h3>
                <span class="px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-sky-500/30 text-sky-200 border border-sky-400/30">
                  {{ activePatient.mrn }}
                </span>
              </div>
              <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 mt-1">
                <span>DOB: <strong>{{ activePatient.date_of_birth }}</strong> ({{ activePatient.age }} years old)</span>
                <span>Gender: <strong>{{ activePatient.gender }}</strong></span>
                <span>Blood: <strong class="text-rose-300">{{ activePatient.blood_group || 'N/A' }}</strong></span>
                <span>Insurance: <strong>{{ activePatient.insurance_provider || 'Self-pay' }}</strong></span>
              </div>
            </div>
          </div>

          <!-- Quick Action & Allergy -->
          <div class="flex flex-wrap items-center gap-3">
            <div v-if="activePatient.allergies" class="px-4 py-2 rounded-xl bg-rose-950/60 border border-rose-800 text-xs text-rose-200 flex items-center gap-2">
              <svg class="w-4 h-4 text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              <div>
                <span class="font-bold">Reported Allergies:</span> {{ activePatient.allergies }}
              </div>
            </div>

            <button
              @click="openBookModal"
              class="px-3.5 py-2 rounded-xl bg-teal-500 hover:bg-teal-600 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Schedule Visit for {{ activePatient.first_name }}
            </button>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto">
        <button
          v-for="t in tabs"
          :key="t.id"
          @click="activeTab = t.id"
          :class="[
            activeTab === t.id
              ? 'bg-slate-900 text-white font-semibold'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60',
            'px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 shrink-0'
          ]"
        >
          <span>{{ t.name }}</span>
          <span :class="[
            activeTab === t.id ? 'bg-sky-500 text-white' : 'bg-slate-200 text-slate-700',
            'px-1.5 py-0.2 rounded-full text-[10px] font-bold'
          ]">{{ t.count }}</span>
        </button>
      </div>

      <!-- Tab Content 1: Encounters & Doctor SOAP Notes -->
      <div v-if="activeTab === 'encounters'" class="space-y-4">
        <div v-if="encounters.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 text-slate-500">
          No signed doctor encounters found for this profile yet.
        </div>
        <div v-for="enc in encounters" :key="enc.id" class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
              <span class="text-xs font-semibold text-slate-400">Consultation Date</span>
              <p class="font-bold text-slate-900 text-base">{{ formatDate(enc.encounter_date) }}</p>
            </div>
            <div class="flex items-center gap-3">
              <div class="text-right">
                <span class="text-xs text-slate-400">Attending Physician</span>
                <p class="font-bold text-slate-800 text-sm">{{ enc.provider?.name }}</p>
                <p class="text-[11px] text-teal-600 font-medium">{{ enc.provider?.specialization }}</p>
              </div>
              <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                Signed Note
              </span>
            </div>
          </div>

          <!-- Recorded Vitals Summary Strip -->
          <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
            <div>
              <span class="text-slate-400 block text-[10px] uppercase font-semibold">Blood Pressure</span>
              <span class="font-bold text-slate-800 text-sm">{{ enc.systolic_bp && enc.diastolic_bp ? `${enc.systolic_bp}/${enc.diastolic_bp} mmHg` : 'N/A' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[10px] uppercase font-semibold">Heart Rate</span>
              <span class="font-bold text-slate-800 text-sm">{{ enc.heart_rate_bpm ? `${enc.heart_rate_bpm} bpm` : 'N/A' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[10px] uppercase font-semibold">Temperature</span>
              <span class="font-bold text-slate-800 text-sm">{{ enc.temperature_c ? `${enc.temperature_c} °C` : 'N/A' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[10px] uppercase font-semibold">Oxygen (SpO2)</span>
              <span class="font-bold text-slate-800 text-sm">{{ enc.spo2_percentage ? `${enc.spo2_percentage}%` : 'N/A' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[10px] uppercase font-semibold">Weight</span>
              <span class="font-bold text-slate-800 text-sm">{{ enc.weight_kg ? `${enc.weight_kg} kg` : 'N/A' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[10px] uppercase font-semibold">Body Mass Index</span>
              <span class="font-bold text-slate-800 text-sm">{{ enc.bmi ? `${enc.bmi} kg/m²` : 'N/A' }}</span>
            </div>
          </div>

          <!-- Diagnoses Tag List -->
          <div v-if="enc.diagnoses && enc.diagnoses.length > 0">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Tagged Diagnoses (ICD-10)</span>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="diag in enc.diagnoses"
                :key="diag.id"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-50 text-teal-800 border border-teal-200"
              >
                <span class="font-mono font-bold text-teal-700">[{{ diag.icd10_code }}]</span>
                <span>{{ diag.description }}</span>
              </span>
            </div>
          </div>

          <!-- Clinical Notes / Plan Summary -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
              <span class="font-bold text-slate-700 block mb-1 uppercase tracking-wide text-[10px]">Assessment Summary</span>
              <p class="text-slate-600 whitespace-pre-line leading-relaxed">{{ enc.assessment_notes || 'No assessment notes recorded.' }}</p>
            </div>
            <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
              <span class="font-bold text-slate-700 block mb-1 uppercase tracking-wide text-[10px]">Treatment Plan & Instructions</span>
              <p class="text-slate-600 whitespace-pre-line leading-relaxed">{{ enc.plan_notes || 'No treatment plan notes recorded.' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Content 2: Prescriptions -->
      <div v-if="activeTab === 'prescriptions'" class="space-y-4">
        <div v-if="prescriptions.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 text-slate-500">
          No active or previous prescriptions found.
        </div>
        <div v-for="rx in prescriptions" :key="rx.id" class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
              <span class="text-xs text-slate-400">Prescription Issued</span>
              <p class="font-bold text-slate-900 text-sm">{{ formatDate(rx.created_at) }} by {{ rx.provider?.name }}</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-sky-100 text-sky-800">
              {{ rx.status }}
            </span>
          </div>

          <!-- Line Items Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                  <th class="py-2.5 px-3">Medication</th>
                  <th class="py-2.5 px-3">Dosage & Frequency</th>
                  <th class="py-2.5 px-3">Duration</th>
                  <th class="py-2.5 px-3">Quantity</th>
                  <th class="py-2.5 px-3">Instructions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="item in rx.items" :key="item.id" class="hover:bg-slate-50/50">
                  <td class="py-3 px-3">
                    <span class="font-bold text-slate-900">{{ item.medicine?.brand_name }}</span>
                    <span class="text-slate-400 block text-[11px]">{{ item.medicine?.generic_name }} ({{ item.medicine?.strength }})</span>
                  </td>
                  <td class="py-3 px-3 font-medium">{{ item.dosage }} • {{ item.frequency }}</td>
                  <td class="py-3 px-3">{{ item.duration_days }} days</td>
                  <td class="py-3 px-3 font-bold">{{ item.quantity }}</td>
                  <td class="py-3 px-3 text-slate-500 italic">{{ item.instructions || 'As directed' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab Content 3: Lab Tests -->
      <div v-if="activeTab === 'lab_orders'" class="space-y-4">
        <div v-if="labOrders.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 text-slate-500">
          No diagnostic lab orders or results found.
        </div>
        <div v-for="order in labOrders" :key="order.id" class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
              <span class="text-xs text-slate-400">Order Placed</span>
              <p class="font-bold text-slate-900 text-sm">{{ formatDate(order.created_at) }} (Priority: {{ order.priority }})</p>
            </div>
            <span :class="[
              order.status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800',
              'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase'
            ]">
              {{ order.status }}
            </span>
          </div>

          <div class="space-y-3">
            <div v-for="item in order.items" :key="item.id" class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-bold text-slate-900 text-sm">{{ item.test?.test_name }}</span>
                  <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-200 text-slate-700">{{ item.test?.test_code }}</span>
                </div>
                <p class="text-slate-400 text-[11px] mt-0.5">Category: {{ item.test?.category }} • Turnaround: {{ item.test?.turnaround_hours }}h</p>
                <div v-if="item.result_value" class="mt-2 text-slate-800 font-medium">
                  <strong>Result:</strong> <span class="font-mono bg-white px-2 py-0.5 rounded border border-slate-200">{{ item.result_value }}</span>
                  <span v-if="item.reference_range" class="text-slate-400 text-[11px] ml-2">(Ref: {{ item.reference_range }})</span>
                </div>
              </div>

              <div class="shrink-0 flex items-center gap-2">
                <span v-if="item.result_status === 'ABNORMAL'" class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                  ABNORMAL
                </span>
                <span v-else-if="item.result_status === 'NORMAL'" class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                  NORMAL
                </span>
                <span v-else class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-600">
                  {{ item.result_status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Content 4: Invoices & Billing -->
      <div v-if="activeTab === 'invoices'" class="space-y-4">
        <div v-if="invoices.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 text-slate-500">
          No billing invoices found.
        </div>
        <div v-for="inv in invoices" :key="inv.id" class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-4">
            <div>
              <span class="font-mono text-xs font-bold text-sky-700">{{ inv.invoice_number }}</span>
              <p class="text-xs text-slate-400">{{ formatDate(inv.created_at) }}</p>
            </div>
            <div class="flex items-center gap-3">
              <span :class="[
                inv.status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800',
                'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider'
              ]">
                {{ inv.status }}
              </span>
              <div class="text-right">
                <span class="text-xs text-slate-400 block">Total Billed</span>
                <span class="font-bold text-slate-900 text-base">${{ inv.grand_total }}</span>
              </div>
            </div>
          </div>

          <!-- Itemized Breakdown -->
          <div class="space-y-1.5 text-xs">
            <div v-for="it in inv.items" :key="it.id" class="flex items-center justify-between py-1 border-b border-slate-50 text-slate-600">
              <span>{{ it.description }}</span>
              <span class="font-mono font-medium text-slate-800">${{ it.total_price }}</span>
            </div>
          </div>

          <div class="pt-3 flex justify-between items-center text-xs text-slate-500">
            <span>Payment Method: <strong class="text-slate-700">{{ inv.payment_method || 'Unpaid' }}</strong></span>
            <span>Balance Remaining: <strong class="text-rose-600 font-mono font-bold">${{ inv.balance_due }}</strong></span>
          </div>
        </div>
      </div>

      <!-- Tab Content 5: Appointments -->
      <div v-if="activeTab === 'appointments'" class="space-y-4">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Scheduled Visits</span>
          <button
            @click="openBookModal"
            class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Book New Appointment
          </button>
        </div>

        <div v-if="appointments.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 text-slate-500 space-y-3">
          <p>No scheduled appointments found for this family profile.</p>
          <button
            @click="openBookModal"
            class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md transition"
          >
            Schedule a Doctor Visit Now &rarr;
          </button>
        </div>

        <div v-for="apt in appointments" :key="apt.id" class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-900 text-base">{{ apt.type }}</span>
              <span :class="[
                apt.status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800',
                'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase'
              ]">
                {{ apt.status }}
              </span>
            </div>
            <p class="text-xs text-slate-600 mt-1">Provider: <strong>{{ apt.provider?.name }}</strong> ({{ apt.provider?.specialization }})</p>
            <p v-if="apt.chief_complaint" class="text-xs text-slate-500 italic mt-0.5">"{{ apt.chief_complaint }}"</p>
          </div>

          <div class="text-right shrink-0">
            <span class="text-xs font-semibold text-slate-400 block">Appointment Schedule</span>
            <span class="font-bold text-slate-900 text-sm">{{ formatDateTime(apt.appointment_date) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal 1: Book Online Appointment -->
    <div v-if="showBookAppointmentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Book Doctor Appointment</h3>
            <p class="text-xs text-slate-500">Choose a family member and specialist physician</p>
          </div>
          <button @click="showBookAppointmentModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitBookAppointment" class="mt-4 space-y-4 text-xs">
          <!-- Family Member Selector -->
          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Select Family Member *</label>
            <select v-model="appointmentForm.patient_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
              <option v-for="m in familyMembers" :key="m.id" :value="m.id">
                {{ m.first_name }} {{ m.last_name }} ({{ m.relationship_to_account_holder }} • {{ m.mrn }})
              </option>
            </select>
          </div>

          <!-- Doctor Selector -->
          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Select Physician / Specialist *</label>
            <select v-model="appointmentForm.provider_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
              <option value="" disabled>Choose physician...</option>
              <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
                {{ doc.name }} ({{ doc.specialization }}) - Consultation Fee: ${{ doc.consultation_fee }}
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Appointment Date & Time *</label>
              <input v-model="appointmentForm.appointment_date" required type="datetime-local" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Visit Type *</label>
              <select v-model="appointmentForm.type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="CONSULTATION">General Consultation</option>
                <option value="ROUTINE_CHECKUP">Routine Wellness Check</option>
                <option value="FOLLOW_UP">Follow-Up Visit</option>
                <option value="SPECIALIST_CARE">Specialist Review</option>
                <option value="PEDIATRIC_CHECKUP">Pediatric Checkup</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Reason for Visit / Symptoms</label>
            <textarea
              v-model="appointmentForm.chief_complaint"
              rows="3"
              placeholder="e.g. Follow-up for blood pressure check, persistent cough, routine physical..."
              class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none"
            ></textarea>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showBookAppointmentModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button :disabled="appointmentForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition">
              {{ appointmentForm.processing ? 'Booking...' : 'Confirm Appointment' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal 2: Add Family Member -->
    <div v-if="showAddMemberModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">Add Household Family Member</h3>
          <button @click="showAddMemberModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitAddMember" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">First Name</label>
              <input v-model="memberForm.first_name" required type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Last Name</label>
              <input v-model="memberForm.last_name" required type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Relationship</label>
              <select v-model="memberForm.relationship_to_account_holder" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                <option value="SPOUSE">Spouse</option>
                <option value="CHILD">Child / Dependent</option>
                <option value="PARENT">Elderly Parent</option>
                <option value="OTHER">Other Household Relative</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Date of Birth</label>
              <input v-model="memberForm.date_of_birth" required type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Gender</label>
              <select v-model="memberForm.gender" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                <option value="MALE">Male</option>
                <option value="FEMALE">Female</option>
                <option value="OTHER">Other</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Blood Group</label>
              <input v-model="memberForm.blood_group" placeholder="e.g. O+, A-, B+" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Known Allergies</label>
            <input v-model="memberForm.allergies" placeholder="e.g. Penicillin, Peanuts, Sulfa (or None)" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showAddMemberModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button :disabled="memberForm.processing" type="submit" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-md shadow-sky-600/20 transition">
              {{ memberForm.processing ? 'Saving...' : 'Create Family Profile' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  account: Object,
  familyMembers: Array,
  activePatient: Object,
  encounters: Array,
  prescriptions: Array,
  labOrders: Array,
  invoices: Array,
  appointments: Array,
  doctors: Array,
});

const activeTab = ref('appointments');
const showAddMemberModal = ref(false);
const showBookAppointmentModal = ref(false);

const tabs = computed(() => [
  { id: 'appointments', name: 'Appointments', count: props.appointments?.length || 0 },
  { id: 'encounters', name: 'Clinical Notes', count: props.encounters?.length || 0 },
  { id: 'prescriptions', name: 'Prescriptions', count: props.prescriptions?.length || 0 },
  { id: 'lab_orders', name: 'Lab Results', count: props.labOrders?.length || 0 },
  { id: 'invoices', name: 'Invoices & Billing', count: props.invoices?.length || 0 },
]);

const memberForm = useForm({
  first_name: '',
  last_name: '',
  relationship_to_account_holder: 'CHILD',
  date_of_birth: '',
  gender: 'MALE',
  blood_group: '',
  allergies: '',
});

const appointmentForm = useForm({
  patient_id: '',
  provider_id: '',
  appointment_date: '',
  type: 'CONSULTATION',
  chief_complaint: '',
});

const openBookModal = () => {
  appointmentForm.patient_id = props.activePatient?.id || props.familyMembers?.[0]?.id || '';
  appointmentForm.provider_id = props.doctors?.[0]?.id || '';
  // Set default appointment date to tomorrow at 10:00 AM
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(10, 0, 0, 0);
  const tzOffset = tomorrow.getTimezoneOffset() * 60000;
  appointmentForm.appointment_date = (new Date(tomorrow - tzOffset)).toISOString().slice(0, 16);
  showBookAppointmentModal.value = true;
};

const switchProfile = (patientId) => {
  router.post('/portal/switch-profile', { patient_id: patientId });
};

const submitAddMember = () => {
  memberForm.post('/portal/add-member', {
    onSuccess: () => {
      showAddMemberModal.value = false;
      memberForm.reset();
    },
  });
};

const submitBookAppointment = () => {
  appointmentForm.post('/portal/book-appointment', {
    onSuccess: () => {
      showBookAppointmentModal.value = false;
      appointmentForm.reset();
      activeTab.value = 'appointments';
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
