<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans text-slate-800">
    <!-- Clean, Focused Patient Portal Navigation Header (No Sidebar, No Staff Nav) -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
        <!-- Brand & Title -->
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-teal-400 flex items-center justify-center text-white font-black text-lg shadow-sm">
            +
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-base font-bold text-slate-900 tracking-tight">Synapse Care</h1>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">Patient Portal</span>
            </div>
            <p class="text-[11px] text-slate-400">Electronic Health Records & Family Medical Hub</p>
          </div>
        </div>

        <!-- Account Info & Sign Out Actions -->
        <div class="flex items-center gap-3">
          <div class="hidden sm:flex flex-col text-right">
            <span class="text-xs font-bold text-slate-800">{{ account.phone_number }}</span>
            <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 justify-end">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified Account
            </span>
          </div>

          <Link
            href="/portal/logout"
            method="post"
            as="button"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 text-xs font-semibold text-slate-600 transition shadow-2xs"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Sign Out
          </Link>
        </div>
      </div>
    </header>

    <!-- Notification Flash Toasts -->
    <div v-if="$page.props.flash?.success" class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 px-4 py-2.5 text-xs font-medium">
      <div class="max-w-7xl mx-auto flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <span>{{ $page.props.flash.success }}</span>
      </div>
    </div>

    <div v-if="$page.props.flash?.error" class="bg-rose-50 border-b border-rose-200 text-rose-800 px-4 py-2.5 text-xs font-medium">
      <div class="max-w-7xl mx-auto flex items-center gap-2">
        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span>{{ $page.props.flash.error }}</span>
      </div>
    </div>

    <div v-if="$page.props.flash?.info" class="bg-sky-50 border-b border-sky-200 text-sky-800 px-4 py-2.5 text-xs font-medium">
      <div class="max-w-7xl mx-auto flex items-center gap-2">
        <svg class="w-4 h-4 text-sky-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <span>{{ $page.props.flash.info }}</span>
      </div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Patient Header & Switcher Bar -->
        <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800">Patient Account</span>
              <span class="text-xs font-mono text-slate-500">{{ account.phone_number }}</span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Patient Portal & Records</h2>
            <p class="text-sm text-slate-500">Select a patient below to book doctor appointments or review clinical records, prescriptions, and lab tests.</p>
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
              Add Patient
            </button>
          </div>
        </div>

        <!-- Patients List / Cards -->
        <div class="mt-6">
          <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Patients</p>
            <span v-if="familyMembers.length > 0" class="text-xs font-semibold text-slate-400">{{ familyMembers.length }} registered</span>
          </div>
          
          <div v-if="familyMembers.length === 0" class="p-8 text-center bg-slate-50 border border-dashed border-slate-300 rounded-2xl">
            <div class="w-12 h-12 mx-auto rounded-full bg-sky-100 text-sky-600 flex items-center justify-center mb-3">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-800">No Patients Registered Yet</h4>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">Get started by adding your first patient profile to book appointments and view medical records.</p>
            <button
              @click="showAddMemberModal = true"
              class="mt-4 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-xs transition inline-flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>+ Add Patient</span>
            </button>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div
              v-for="(member, index) in familyMembers"
              :key="member.id"
              @click="switchProfile(member.id)"
              :class="[
                activePatient?.id === member.id
                  ? 'border-sky-500 ring-2 ring-sky-500/20 bg-sky-50/50 shadow-sm'
                  : 'border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50/80',
                'p-4 rounded-xl border cursor-pointer transition flex items-start gap-3 relative'
              ]"
            >
              <!-- Avatar -->
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
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-sky-100 text-sky-800">
                    Patient {{ index + 1 }}
                  </span>
                  <span class="text-xs text-slate-400">{{ member.age }} yrs • {{ member.gender }}</span>
                </div>
                <p class="text-[11px] font-mono text-slate-500 mt-1 truncate">{{ member.mrn }}</p>
              </div>

              <div class="flex items-center gap-1.5 shrink-0">
                <button
                  @click.stop="openEditPatientModal(member)"
                  title="Edit Patient Details"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <span v-if="activePatient?.id === member.id" class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
              </div>
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
          <div class="flex flex-wrap items-center gap-2.5">
            <div v-if="activePatient.allergies" class="px-3.5 py-2 rounded-xl bg-rose-950/60 border border-rose-800 text-xs text-rose-200 flex items-center gap-2">
              <svg class="w-4 h-4 text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              <div>
                <span class="font-bold">Reported Allergies:</span> {{ activePatient.allergies }}
              </div>
            </div>

            <button
              @click="openEditPatientModal(activePatient)"
              class="px-3.5 py-2 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700/80 shadow-xs transition flex items-center gap-1.5"
            >
              <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              <span>Edit Patient</span>
            </button>

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
       

        <div v-if="appointments.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 text-slate-500 space-y-3">
          <p>No scheduled appointments found for this family profile.</p>
          <button
            @click="openBookModal"
            class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md transition"
          >
            Schedule a Doctor Visit Now &rarr;
          </button>
        </div>

        <div v-for="apt in appointments" :key="apt.id" class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
              <!-- Prominent Daily Serial Badge -->
              <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-teal-600 to-emerald-500 text-white font-mono font-black flex items-center justify-center text-lg shrink-0 shadow-xs">
                #{{ apt.serial_number || '-' }}
              </div>

              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-bold text-slate-900 text-base">{{ apt.type }}</span>
                  <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                    {{ apt.token_number || ('TK-' + String(apt.serial_number).padStart(3, '0')) }}
                  </span>
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                      apt.visit_type === 'REPORT_VISIT'
                        ? 'bg-amber-100 text-amber-800 border border-amber-300'
                        : 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                    ]"
                  >
                    {{ apt.visit_type === 'REPORT_VISIT' ? 'Report Visit / Follow-up' : 'First / New Patient' }}
                  </span>
                  <span :class="[
                    apt.payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800',
                    'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase'
                  ]">
                    {{ apt.payment_status }} (${{ Number(apt.fee_amount || 0).toFixed(2) }})
                  </span>
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
            </div>

            <div class="text-right shrink-0">
              <span class="text-xs font-semibold text-slate-400 block">Appointment Schedule</span>
              <span class="font-bold text-slate-900 text-sm">{{ formatDateTime(apt.appointment_date) }}</span>
            </div>
          </div>

          <!-- Phase 1: Automated SMS & Email Notification Dispatch Preview -->
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs space-y-1">
            <div class="flex items-center gap-2 text-slate-700 font-semibold text-[11px]">
              <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span>Automated SMS & Email Notification Sent</span>
              <span class="text-[10px] text-slate-400">&bull; Daily Serial Dispatched</span>
            </div>
            <p class="text-slate-500 font-mono text-[11px]">
              {{ apt.notification_payload?.sms_body || `Hi ${activePatient?.first_name}, your appointment with ${apt.provider?.name} is confirmed for ${formatDate(apt.appointment_date)}. Serial: #${apt.serial_number} (${apt.token_number}). Please arrive 15m early.` }}
            </p>
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
          <!-- Patient Selector -->
          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Select Patient *</label>
            <select v-model="appointmentForm.patient_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
              <option v-for="(m, index) in familyMembers" :key="m.id" :value="m.id">
                Patient {{ index + 1 }}: {{ m.first_name }} {{ m.last_name }} ({{ m.mrn }})
              </option>
            </select>
          </div>

          <!-- Department Selector -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px]">
                1. Select Clinical Department *
              </label>
              <span class="text-[10px] text-teal-600 font-bold">
                {{ filteredDoctors.length }} doctor{{ filteredDoctors.length !== 1 ? 's' : '' }} available
              </span>
            </div>
            <div class="flex flex-wrap gap-1.5">
              <button
                type="button"
                v-for="dept in availableDepartments"
                :key="dept"
                @click="selectDepartment(dept)"
                :class="[
                  selectedDepartment === dept
                    ? 'bg-slate-900 text-white font-bold ring-2 ring-slate-700 shadow-xs'
                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200',
                  'px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5'
                ]"
              >
                <span v-if="dept === 'Medicine'" class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span v-else-if="dept === 'Neurology'" class="w-2 h-2 rounded-full bg-purple-400"></span>
                <span v-else-if="dept === 'Arthrology & Orthopedics'" class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span v-else-if="dept === 'Cardiology'" class="w-2 h-2 rounded-full bg-rose-400"></span>
                <span v-else class="w-2 h-2 rounded-full bg-teal-400"></span>
                {{ dept === 'ALL' ? 'All Departments' : dept }}
              </button>
            </div>
          </div>

          <!-- Doctor Selector -->
          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1.5">
              2. Select Doctor in {{ selectedDepartment === 'ALL' ? 'Any Department' : selectedDepartment }} *
            </label>

            <!-- Interactive Visual Doctor Cards List -->
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1 mb-2">
              <div
                v-for="doc in filteredDoctors"
                :key="doc.id"
                @click="appointmentForm.provider_id = doc.id"
                :class="[
                  appointmentForm.provider_id === doc.id
                    ? 'bg-teal-50 border-teal-500 ring-2 ring-teal-400/20 shadow-xs'
                    : 'bg-white border-slate-200 hover:border-teal-300 hover:bg-slate-50',
                  'p-2.5 rounded-xl border cursor-pointer transition flex items-center justify-between gap-3'
                ]"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <div
                    :class="[
                      appointmentForm.provider_id === doc.id
                        ? 'bg-teal-600 text-white'
                        : 'bg-teal-100 text-teal-800',
                      'w-8 h-8 rounded-full font-bold flex items-center justify-center text-xs shrink-0 transition'
                    ]"
                  >
                    Dr
                  </div>
                  <div class="min-w-0">
                    <div class="font-bold text-slate-900 text-xs truncate flex items-center gap-1.5">
                      <span>{{ doc.name }}</span>
                      <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                        {{ doc.department }}
                      </span>
                    </div>
                    <div class="text-[11px] text-slate-500 truncate">{{ doc.specialization }}</div>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <div class="text-xs font-black text-teal-700">${{ Number(doc.consultation_fee || 75).toFixed(0) }}</div>
                  <div class="text-[9px] text-slate-400">Follow-up: ${{ Number(doc.followup_fee || 30).toFixed(0) }}</div>
                </div>
              </div>
            </div>

            <!-- Fallback select -->
            <select v-model="appointmentForm.provider_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
              <option value="" disabled>Choose physician...</option>
              <option v-for="doc in filteredDoctors" :key="doc.id" :value="doc.id">
                {{ doc.name }} &bull; {{ doc.department }} ({{ doc.specialization }}) &bull; First Visit: ${{ Number(doc.consultation_fee || 75).toFixed(0) }}
              </option>
            </select>
          </div>

          <!-- Phase 1: Visit Categorization (New Patient vs Report Visit) -->
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <span class="block font-bold text-slate-800 uppercase tracking-wide text-[10px] mb-2">Consultation Visit Type *</span>
            <div class="grid grid-cols-2 gap-2">
              <label
                :class="[
                  appointmentForm.visit_type === 'NEW_PATIENT'
                    ? 'bg-emerald-50 border-emerald-500 text-emerald-900 font-bold ring-2 ring-emerald-400/20'
                    : 'bg-white border-slate-200 text-slate-700',
                  'p-2.5 rounded-xl border cursor-pointer text-center transition'
                ]"
              >
                <input type="radio" v-model="appointmentForm.visit_type" value="NEW_PATIENT" class="sr-only" />
                <div class="text-xs">First / New Visit</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Standard Consultation Fee</div>
              </label>

              <label
                :class="[
                  appointmentForm.visit_type === 'REPORT_VISIT'
                    ? 'bg-amber-50 border-amber-500 text-amber-900 font-bold ring-2 ring-amber-400/20'
                    : 'bg-white border-slate-200 text-slate-700',
                  'p-2.5 rounded-xl border cursor-pointer text-center transition'
                ]"
              >
                <input type="radio" v-model="appointmentForm.visit_type" value="REPORT_VISIT" class="sr-only" />
                <div class="text-xs">Report / Follow-up</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Follow-up Review Fee</div>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Appointment Date & Time *</label>
              <input v-model="appointmentForm.appointment_date" required type="datetime-local" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Encounter Type *</label>
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

    <!-- Modal 2: Add Patient -->
    <div v-if="showAddMemberModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Add Patient</h3>
            <p class="text-xs text-slate-500">Enter personal and medical details</p>
          </div>
          <button @click="showAddMemberModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitAddMember" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">First Name *</label>
              <input v-model="memberForm.first_name" required type="text" placeholder="First name" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Last Name *</label>
              <input v-model="memberForm.last_name" required type="text" placeholder="Last name" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Date of Birth * (Max Today)</label>
              <input v-model="memberForm.date_of_birth" :max="todayDate" required type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Gender *</label>
              <select v-model="memberForm.gender" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                <option value="MALE">Male</option>
                <option value="FEMALE">Female</option>
                <option value="OTHER">Other</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Blood Group (Fixed)</label>
              <select v-model="memberForm.blood_group" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                <option value="">Select Blood Group...</option>
                <option v-for="bg in bloodGroups" :key="bg" :value="bg">{{ bg }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Known Allergies</label>
              <input v-model="memberForm.allergies" placeholder="e.g. Penicillin, Peanuts (or None)" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showAddMemberModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button :disabled="memberForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-md shadow-sky-600/20 transition">
              {{ memberForm.processing ? 'Saving...' : 'Add Patient' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal 3: Edit Patient Details -->
    <div v-if="showEditPatientModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Edit Patient Details</h3>
            <p class="text-xs text-slate-500">Update medical and contact records for {{ editingPatient?.first_name }} {{ editingPatient?.last_name }}</p>
          </div>
          <button @click="showEditPatientModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitEditPatient" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">First Name *</label>
              <input v-model="editPatientForm.first_name" required type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Last Name *</label>
              <input v-model="editPatientForm.last_name" required type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Date of Birth * (Max Today)</label>
              <input v-model="editPatientForm.date_of_birth" :max="todayDate" required type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Gender *</label>
              <select v-model="editPatientForm.gender" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                <option value="MALE">Male</option>
                <option value="FEMALE">Female</option>
                <option value="OTHER">Other</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Blood Group (Fixed)</label>
              <select v-model="editPatientForm.blood_group" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                <option value="">Select Blood Group...</option>
                <option v-for="bg in bloodGroups" :key="bg" :value="bg">{{ bg }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Known Allergies</label>
              <input v-model="editPatientForm.allergies" placeholder="e.g. Penicillin, Peanuts (or None)" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Insurance Provider</label>
              <input v-model="editPatientForm.insurance_provider" placeholder="e.g. Blue Cross Blue Shield" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Policy Number</label>
              <input v-model="editPatientForm.insurance_policy_number" placeholder="e.g. BCBS-12345" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Emergency Contact Person</label>
              <input v-model="editPatientForm.emergency_contact_name" placeholder="Contact Name" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Emergency Phone</label>
              <input v-model="editPatientForm.emergency_contact_phone" placeholder="+15550000000" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Residential Address</label>
            <input v-model="editPatientForm.address" placeholder="Street, City, Postal Code" type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none" />
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showEditPatientModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button :disabled="editPatientForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-md shadow-sky-600/20 transition">
              {{ editPatientForm.processing ? 'Saving...' : 'Save Patient Details' }}
            </button>
          </div>
        </form>
      </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';

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
const showEditPatientModal = ref(false);
const showBookAppointmentModal = ref(false);
const editingPatient = ref(null);

const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'UNKNOWN'];
const todayDate = computed(() => new Date().toISOString().split('T')[0]);

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
  relationship_to_account_holder: 'PATIENT',
  date_of_birth: '',
  gender: 'MALE',
  blood_group: '',
  allergies: '',
});

const editPatientForm = useForm({
  first_name: '',
  last_name: '',
  date_of_birth: '',
  gender: 'MALE',
  blood_group: '',
  allergies: '',
  insurance_provider: '',
  insurance_policy_number: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  address: '',
});

const appointmentForm = useForm({
  patient_id: '',
  provider_id: '',
  appointment_date: '',
  visit_type: 'NEW_PATIENT',
  type: 'CONSULTATION',
  chief_complaint: '',
});

const selectedDepartment = ref('ALL');

const availableDepartments = computed(() => {
  const depts = new Set((props.doctors || []).map(d => d.department).filter(Boolean));
  return ['ALL', ...Array.from(depts)];
});

const filteredDoctors = computed(() => {
  if (selectedDepartment.value === 'ALL') {
    return props.doctors || [];
  }
  return (props.doctors || []).filter(d => d.department === selectedDepartment.value);
});

const selectDepartment = (dept) => {
  selectedDepartment.value = dept;
  const docs = filteredDoctors.value;
  if (!docs.some(d => d.id === appointmentForm.provider_id)) {
    appointmentForm.provider_id = docs[0]?.id || '';
  }
};

const openBookModal = () => {
  selectedDepartment.value = 'ALL';
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

const openEditPatientModal = (patient = null) => {
  const target = patient || props.activePatient;
  if (!target) return;
  editingPatient.value = target;
  editPatientForm.first_name = target.first_name || '';
  editPatientForm.last_name = target.last_name || '';
  editPatientForm.date_of_birth = target.date_of_birth ? target.date_of_birth.split('T')[0] : '';
  editPatientForm.gender = target.gender || 'MALE';
  editPatientForm.blood_group = target.blood_group || '';
  editPatientForm.allergies = target.allergies || '';
  editPatientForm.insurance_provider = target.insurance_provider || '';
  editPatientForm.insurance_policy_number = target.insurance_policy_number || '';
  editPatientForm.emergency_contact_name = target.emergency_contact_name || '';
  editPatientForm.emergency_contact_phone = target.emergency_contact_phone || '';
  editPatientForm.address = target.address || '';
  showEditPatientModal.value = true;
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

const submitEditPatient = () => {
  if (!editingPatient.value) return;
  editPatientForm.put(`/portal/patient/${editingPatient.value.id}`, {
    onSuccess: () => {
      showEditPatientModal.value = false;
      editingPatient.value = null;
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
