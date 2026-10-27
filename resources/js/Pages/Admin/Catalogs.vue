<template>
  <AuthenticatedLayout>
    <!-- Full-Width Responsive Container (Zero Wasted Space) -->
    <div class="w-full space-y-3.5 pb-8">
      
      <!-- Top Executive Header & Quick KPI Bar -->
      <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Title & Badges -->
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                Hospital Master Station
              </span>
              <span class="text-xs text-slate-400 font-medium hidden sm:inline">&bull;</span>
              <span class="text-xs text-slate-500 font-medium">Enterprise Governance, Staff Rostering & Clinical Ledgers</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
              Hospital Master Administration & Setup
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
              Longitudinal patient histories, itemized prescriptions, appointment queues, doctor fees by department, and master catalogs.
            </p>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex items-center gap-2 flex-wrap shrink-0">
            <button
              @click="showAddStaffModal = true"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Create Staff Account</span>
            </button>
            <button
              @click="showAddMedModal = true"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Add Medicine</span>
            </button>
            <button
              @click="showAddLabModal = true"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 text-xs font-bold transition"
            >
              <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Add Lab Test</span>
            </button>
          </div>
        </div>

        <!-- High-Density Metric Strip (Always Visible) -->
        <div class="mt-4 pt-3.5 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
          <div @click="activeTab = 'patients'" class="p-2.5 rounded-xl bg-sky-50/70 border border-sky-100 cursor-pointer hover:bg-sky-100/70 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700">Patients</span>
            <div class="text-lg font-black text-slate-900">{{ stats?.total_patients ?? patients.length }}</div>
          </div>
          <div @click="activeTab = 'appointments'" class="p-2.5 rounded-xl bg-teal-50/70 border border-teal-100 cursor-pointer hover:bg-teal-100/70 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700">Visits Taken</span>
            <div class="text-lg font-black text-slate-900">{{ stats?.total_appointments ?? appointments.length }}</div>
          </div>
          <div @click="activeTab = 'prescriptions'" class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100 cursor-pointer hover:bg-emerald-100/70 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Prescriptions</span>
            <div class="text-lg font-black text-slate-900">{{ stats?.total_prescriptions ?? prescriptions.length }}</div>
          </div>
          <div @click="activeTab = 'staff_setup'; staffSubTab = 'doctors'" class="p-2.5 rounded-xl bg-purple-50/70 border border-purple-100 cursor-pointer hover:bg-purple-100/70 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Doctors</span>
            <div class="text-lg font-black text-slate-900">{{ doctors.length }}</div>
          </div>
          <div @click="activeTab = 'staff_setup'; staffSubTab = 'nurses'" class="p-2.5 rounded-xl bg-cyan-50/70 border border-cyan-100 cursor-pointer hover:bg-cyan-100/70 transition">
            <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-700">Ward Nurses</span>
            <div class="text-lg font-black text-slate-900">{{ nurses.length }}</div>
          </div>
          <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-100">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Total Revenue</span>
            <div class="text-lg font-black text-slate-900">${{ Number(stats?.total_revenue || 0).toFixed(0) }}</div>
          </div>
        </div>

        <!-- Master Navigation Tab Bar -->
        <div class="mt-4 flex items-center gap-1.5 overflow-x-auto pb-0.5 border-t border-slate-100 pt-3">
          <button
            v-for="t in primaryTabs"
            :key="t.id"
            @click="activeTab = t.id"
            :class="[
              activeTab === t.id
                ? 'bg-slate-900 text-white font-bold shadow-xs ring-1 ring-slate-800'
                : 'bg-slate-100/80 text-slate-600 hover:bg-slate-200/70 hover:text-slate-900',
              'px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-2 shrink-0'
            ]"
          >
            <span>{{ t.label }}</span>
            <span
              v-if="t.count !== null"
              :class="[
                activeTab === t.id ? 'bg-purple-500 text-white' : 'bg-slate-200 text-slate-700',
                'px-1.5 py-0.2 rounded-full text-[10px] font-bold'
              ]"
            >
              {{ t.count }}
            </span>
          </button>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 1: PATIENTS & DETAILED LONGITUDINAL HISTORY (UNIFIED CARD) -->
      <!-- ============================================================== -->
      <div v-if="activeTab === 'patients'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <!-- Integrated Header with Filters & Search in ONE Place (No Double Boxes) -->
        <div class="p-3.5 sm:p-4 border-b border-slate-200 bg-slate-50/70 flex flex-col lg:flex-row items-center justify-between gap-3">
          <div class="flex items-center gap-2.5 self-start lg:self-auto">
            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-bold text-slate-900 text-sm">Patient Master Directory</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                  {{ filteredPatients.length }} Patients
                </span>
              </div>
              <p class="text-[11px] text-slate-500">Longitudinal clinical histories, SOAP encounter notes & visit counter</p>
            </div>
          </div>

          <!-- Middle Filter Pills -->
          <div class="flex items-center gap-1.5 overflow-x-auto w-full lg:w-auto">
            <button
              v-for="flt in patientFilters"
              :key="flt.id"
              @click="selectedPatientFilter = flt.id"
              :class="[
                selectedPatientFilter === flt.id
                  ? 'bg-slate-900 text-white font-bold shadow-xs'
                  : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200',
                'px-2.5 py-1.5 rounded-xl text-xs transition shrink-0'
              ]"
            >
              {{ flt.label }}
            </button>
          </div>

          <!-- Search Input -->
          <div class="relative w-full lg:w-72 self-end lg:self-auto">
            <input
              v-model="searchPatient"
              type="text"
              placeholder="Search name, MRN, phone, blood..."
              class="w-full pl-8 pr-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none"
            />
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Full-Width Patients Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Patient Profile</th>
                <th class="py-3 px-4">Demographics & Blood</th>
                <th class="py-3 px-4">Contact Phone</th>
                <th class="py-3 px-4 text-center">Total Visits Taken</th>
                <th class="py-3 px-4 text-center">Prescriptions</th>
                <th class="py-3 px-4">Last Visit & Physician</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-if="filteredPatients.length === 0">
                <td colspan="7" class="py-8 text-center text-slate-400">No patient records found matching filter.</td>
              </tr>
              <tr v-for="p in filteredPatients" :key="p.id" class="hover:bg-slate-50/70 transition">
                <!-- Patient Profile -->
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-900 text-sm">{{ p.first_name }} {{ p.last_name }}</div>
                  <div class="text-slate-400 font-mono text-[11px]">{{ p.mrn }}</div>
                </td>

                <!-- Demographics -->
                <td class="py-3 px-4">
                  <div>{{ p.age }} yrs &bull; {{ p.gender }}</div>
                  <div class="mt-0.5">
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase">
                      {{ p.blood_group || 'N/A' }}
                    </span>
                  </div>
                </td>

                <!-- Contact -->
                <td class="py-3 px-4 font-mono text-slate-600">
                  {{ p.phone || 'N/A' }}
                </td>

                <!-- Appointments Taken Count (Prominent Badge) -->
                <td class="py-3 px-4 text-center">
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-teal-100 text-teal-800 border border-teal-300 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    #{{ p.appointments_count }} Visits Taken
                  </span>
                </td>

                <!-- Prescriptions Count -->
                <td class="py-3 px-4 text-center">
                  <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ p.prescriptions_count }} Prescriptions
                  </span>
                </td>

                <!-- Last Visit -->
                <td class="py-3 px-4 text-xs">
                  <div v-if="p.appointments?.length > 0">
                    <span class="font-semibold text-slate-800 block">{{ formatDate(p.appointments[0].appointment_date) }}</span>
                    <span class="text-slate-400 text-[11px]">Dr. {{ p.appointments[0].provider?.name }}</span>
                  </div>
                  <div v-else class="text-slate-400 italic text-[11px]">No visits recorded</div>
                </td>

                <!-- Action Button -->
                <td class="py-3 px-4 text-right">
                  <button
                    @click="openPatientHistory(p)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-purple-700 text-white font-bold text-xs shadow-xs transition"
                  >
                    <span>View Full History</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 2: ALL PRESCRIPTIONS LEDGER (UNIFIED CARD) -->
      <!-- ============================================================== -->
      <div v-if="activeTab === 'prescriptions'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3.5 sm:p-4 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row items-center justify-between gap-3">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-slate-900 text-sm">Hospital Prescriptions Ledger</h3>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                {{ filteredPrescriptions.length }} Records
              </span>
            </div>
            <p class="text-[11px] text-slate-500">Every medication prescribed, strength, dosage schedule, and instructions</p>
          </div>
          <div class="relative w-full sm:w-80">
            <input
              v-model="searchPrescription"
              type="text"
              placeholder="Search patient, doctor, medicine..."
              class="w-full pl-8 pr-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"
            />
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Date Issued</th>
                <th class="py-3 px-4">Patient Details</th>
                <th class="py-3 px-4">Prescribing Physician</th>
                <th class="py-3 px-4">Prescribed Medicines & Dosage</th>
                <th class="py-3 px-4">Doctor Notes / Instructions</th>
                <th class="py-3 px-4 text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-if="filteredPrescriptions.length === 0">
                <td colspan="6" class="py-8 text-center text-slate-400">No prescriptions found matching filter.</td>
              </tr>
              <tr v-for="rx in filteredPrescriptions" :key="rx.id" class="hover:bg-slate-50/70 transition">
                <td class="py-3 px-4 font-mono text-slate-600">
                  <span class="font-bold text-slate-900 block">{{ formatDate(rx.created_at) }}</span>
                  <span class="text-[10px] text-slate-400">{{ formatTime(rx.created_at) }}</span>
                </td>
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-900">{{ rx.patient?.first_name }} {{ rx.patient?.last_name }}</div>
                  <div class="text-[11px] font-mono text-slate-400">{{ rx.patient?.mrn }} &bull; {{ rx.patient?.gender }}</div>
                </td>
                <td class="py-3 px-4">
                  <div class="font-semibold text-slate-800">{{ rx.provider?.name }}</div>
                  <div class="text-[11px] text-slate-400">{{ rx.provider?.department || 'Consultant' }}</div>
                </td>
                <td class="py-3 px-4">
                  <div class="space-y-1">
                    <div v-for="item in rx.items" :key="item.id" class="flex items-center gap-1.5 flex-wrap">
                      <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200">
                        {{ item.medicine?.brand_name || item.medicine?.generic_name }} ({{ item.medicine?.strength }})
                      </span>
                      <span class="text-[11px] text-slate-500">{{ item.dosage }} &bull; {{ item.frequency }} ({{ item.duration_days }}d)</span>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4 text-slate-500 italic max-w-xs truncate">
                  {{ rx.notes || 'Take as directed.' }}
                </td>
                <td class="py-3 px-4 text-right">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                    {{ rx.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 3: ALL APPOINTMENTS REGISTER (UNIFIED CARD) -->
      <!-- ============================================================== -->
      <div v-if="activeTab === 'appointments'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3.5 sm:p-4 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row items-center justify-between gap-3">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-slate-900 text-sm">Hospital Appointments Register</h3>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                {{ filteredAppointments.length }} Appointments
              </span>
            </div>
            <p class="text-[11px] text-slate-500">Track all booked outpatient appointments, visit categorization, and fee collections</p>
          </div>
          <div class="relative w-full sm:w-80">
            <input
              v-model="searchAppointment"
              type="text"
              placeholder="Search patient, doctor, token..."
              class="w-full pl-8 pr-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"
            />
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Serial & Token</th>
                <th class="py-3 px-4">Appointment Date</th>
                <th class="py-3 px-4">Patient Profile</th>
                <th class="py-3 px-4">Attending Doctor</th>
                <th class="py-3 px-4">Visit Type</th>
                <th class="py-3 px-4">Consultation Fee</th>
                <th class="py-3 px-4 text-right">Queue Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-if="filteredAppointments.length === 0">
                <td colspan="7" class="py-8 text-center text-slate-400">No appointments found matching filter.</td>
              </tr>
              <tr v-for="apt in filteredAppointments" :key="apt.id" class="hover:bg-slate-50/70 transition">
                <td class="py-3 px-4 font-mono">
                  <span class="font-extrabold text-teal-700 text-sm">#{{ apt.serial_number }}</span>
                  <span class="text-[10px] text-slate-500 block">Token: {{ apt.token_number }}</span>
                </td>
                <td class="py-3 px-4">
                  <span class="font-bold text-slate-900 block">{{ formatDate(apt.appointment_date) }}</span>
                  <span class="text-[10px] text-slate-400">{{ formatTime(apt.appointment_date) }}</span>
                </td>
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-900">{{ apt.patient?.first_name }} {{ apt.patient?.last_name }}</div>
                  <div class="text-[11px] font-mono text-slate-400">{{ apt.patient?.mrn }} &bull; {{ apt.patient?.phone }}</div>
                </td>
                <td class="py-3 px-4">
                  <div class="font-semibold text-slate-800">Dr. {{ apt.provider?.name }}</div>
                  <div class="text-[11px] text-slate-400">{{ apt.provider?.department }} &bull; {{ apt.provider?.specialization }}</div>
                </td>
                <td class="py-3 px-4">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                      apt.visit_type === 'REPORT_VISIT'
                        ? 'bg-amber-100 text-amber-800 border border-amber-300'
                        : 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                    ]"
                  >
                    {{ apt.visit_type === 'REPORT_VISIT' ? 'Report Visit ($30)' : 'New Patient ($75)' }}
                  </span>
                </td>
                <td class="py-3 px-4 font-mono">
                  <div class="font-bold text-slate-900">${{ Number(apt.fee_amount || 0).toFixed(2) }}</div>
                  <span
                    :class="[
                      'px-1.5 py-0.2 rounded text-[10px] font-bold uppercase',
                      apt.payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                    ]"
                  >
                    {{ apt.payment_status }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right">
                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                      apt.status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' :
                      apt.status === 'IN_PROGRESS' ? 'bg-amber-100 text-amber-800 animate-pulse' :
                      apt.status === 'CHECKED_IN' ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-700'
                    ]"
                  >
                    {{ apt.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 4: HOSPITAL STAFF SETUP (THE SETUP HUB!) -->
      <!-- ============================================================== -->
      <div v-if="activeTab === 'staff_setup'" class="space-y-3.5">
        <!-- Sub-Tabs for Staff Setup -->
        <div class="bg-white rounded-2xl p-3 border border-slate-200 shadow-xs flex items-center justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-1.5 overflow-x-auto">
            <button
              @click="staffSubTab = 'doctors'"
              :class="[
                staffSubTab === 'doctors'
                  ? 'bg-emerald-700 text-white font-bold shadow-xs'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200',
                'px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-2'
              ]"
            >
              <span>👨‍⚕️ Doctors by Department</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-emerald-900/40 text-emerald-100">{{ doctors.length }}</span>
            </button>
            <button
              @click="staffSubTab = 'nurses'"
              :class="[
                staffSubTab === 'nurses'
                  ? 'bg-cyan-700 text-white font-bold shadow-xs'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200',
                'px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-2'
              ]"
            >
              <span>🩺 Staff Nurses by Ward</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-cyan-900/40 text-cyan-100">{{ nurses.length }}</span>
            </button>
            <button
              @click="staffSubTab = 'receptionists'"
              :class="[
                staffSubTab === 'receptionists'
                  ? 'bg-teal-700 text-white font-bold shadow-xs'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200',
                'px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-2'
              ]"
            >
              <span>🏢 Receptionists & Front Desk</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-teal-900/40 text-teal-100">{{ receptionists.length }}</span>
            </button>
            <button
              @click="staffSubTab = 'management'"
              :class="[
                staffSubTab === 'management'
                  ? 'bg-purple-700 text-white font-bold shadow-xs'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200',
                'px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-2'
              ]"
            >
              <span>⚖️ Governance & Billing</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-purple-900/40 text-purple-100">{{ managementStaff.length }}</span>
            </button>
          </div>

          <button
            @click="showAddStaffModal = true"
            class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5 ml-auto"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Add Staff Member</span>
          </button>
        </div>

        <!-- 1. DOCTORS BY CLINICAL DEPARTMENT TABLE -->
        <div v-if="staffSubTab === 'doctors'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
          <!-- Department Filter Bar -->
          <div class="p-3.5 sm:p-4 border-b border-slate-200 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
              <h3 class="font-bold text-slate-900 text-sm">Physicians & Consultation Fee Governance</h3>
            </div>

            <!-- Department Filter Pills -->
            <div class="flex items-center gap-1.5 flex-wrap">
              <button
                v-for="dept in doctorDepartments"
                :key="dept"
                @click="doctorDeptFilter = dept"
                :class="[
                  doctorDeptFilter === dept
                    ? 'bg-emerald-700 text-white font-bold shadow-xs'
                    : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200',
                  'px-2.5 py-1 rounded-xl text-xs transition flex items-center gap-1.5'
                ]"
              >
                <span v-if="dept === 'Medicine'" class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span v-else-if="dept === 'Neurology'" class="w-2 h-2 rounded-full bg-purple-400"></span>
                <span v-else-if="dept === 'Arthrology & Orthopedics'" class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span v-else-if="dept === 'Cardiology'" class="w-2 h-2 rounded-full bg-rose-400"></span>
                {{ dept === 'ALL' ? 'All Departments' : dept }}
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                  <th class="py-3 px-4">Doctor Name</th>
                  <th class="py-3 px-4">License / Reg #</th>
                  <th class="py-3 px-4">Department</th>
                  <th class="py-3 px-4">Specialization</th>
                  <th class="py-3 px-4">New Patient Fee</th>
                  <th class="py-3 px-4">Report / Follow-up Fee</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="d in filteredAdminDoctors" :key="d.id" class="hover:bg-slate-50/70 transition">
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-900 text-sm">{{ d.name }}</div>
                    <div class="text-slate-400 text-[11px]">{{ d.email }}</div>
                  </td>
                  <td class="py-3 px-4 font-mono font-semibold text-slate-700">
                    {{ d.license_number || 'N/A' }}
                  </td>
                  <td class="py-3 px-4">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded text-[10px] font-bold uppercase border',
                        d.department === 'Medicine' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' :
                        d.department === 'Neurology' ? 'bg-purple-100 text-purple-800 border-purple-300' :
                        d.department === 'Arthrology & Orthopedics' ? 'bg-amber-100 text-amber-800 border-amber-300' :
                        d.department === 'Cardiology' ? 'bg-rose-100 text-rose-800 border-rose-300' :
                        'bg-teal-100 text-teal-800 border-teal-300'
                      ]"
                    >
                      {{ d.department }}
                    </span>
                  </td>
                  <td class="py-3 px-4 font-medium text-slate-800">
                    {{ d.specialization }}
                  </td>
                  <td class="py-3 px-4 font-mono font-bold text-emerald-700 text-sm">
                    ${{ Number(d.consultation_fee || 75).toFixed(2) }}
                  </td>
                  <td class="py-3 px-4 font-mono font-bold text-amber-700 text-sm">
                    ${{ Number(d.followup_fee || 30).toFixed(2) }}
                  </td>
                  <td class="py-3 px-4">
                    <span :class="d.is_active ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                      {{ d.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      @click="openEditStaffModal(d)"
                      class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition"
                    >
                      Configure Fees & Profile
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 2. NURSES BY WARD TABLE -->
        <div v-if="staffSubTab === 'nurses'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
          <div class="p-3.5 sm:p-4 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
              <h3 class="font-bold text-slate-900 text-sm">Staff Nurses & Clinical Ward Assignments</h3>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">
              {{ nurses.length }} Nurses
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                  <th class="py-3 px-4">Nurse Name</th>
                  <th class="py-3 px-4">License #</th>
                  <th class="py-3 px-4">Ward / Clinical Department</th>
                  <th class="py-3 px-4">Specialization</th>
                  <th class="py-3 px-4">Phone</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="n in nurses" :key="n.id" class="hover:bg-slate-50/70 transition">
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-900 text-sm">{{ n.name }}</div>
                    <div class="text-slate-400 text-[11px]">{{ n.email }}</div>
                  </td>
                  <td class="py-3 px-4 font-mono font-semibold text-slate-700">
                    {{ n.license_number || 'RN-PENDING' }}
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-cyan-100 text-cyan-800 border border-cyan-300">
                      {{ n.department }}
                    </span>
                  </td>
                  <td class="py-3 px-4 font-medium text-slate-800">
                    {{ n.specialization }}
                  </td>
                  <td class="py-3 px-4 font-mono text-slate-600">
                    {{ n.phone || 'N/A' }}
                  </td>
                  <td class="py-3 px-4">
                    <span :class="n.is_active ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                      {{ n.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      @click="openEditStaffModal(n)"
                      class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition"
                    >
                      Edit Account
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 3. RECEPTIONISTS TABLE -->
        <div v-if="staffSubTab === 'receptionists'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
          <div class="p-3.5 sm:p-4 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
              <h3 class="font-bold text-slate-900 text-sm">Receptionists & Outpatient Cashiers</h3>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800 border border-teal-200">
              {{ receptionists.length }} Receptionists
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                  <th class="py-3 px-4">Receptionist Name</th>
                  <th class="py-3 px-4">Email</th>
                  <th class="py-3 px-4">Department / Desk</th>
                  <th class="py-3 px-4">Phone</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="r in receptionists" :key="r.id" class="hover:bg-slate-50/70 transition">
                  <td class="py-3 px-4 font-bold text-slate-900 text-sm">
                    {{ r.name }}
                  </td>
                  <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                    {{ r.email }}
                  </td>
                  <td class="py-3 px-4">
                    <div class="font-semibold text-slate-800">{{ r.department || 'Front Desk' }}</div>
                    <div class="text-slate-400 text-[11px]">{{ r.specialization || 'Cashier' }}</div>
                  </td>
                  <td class="py-3 px-4 font-mono text-slate-600">
                    {{ r.phone || 'N/A' }}
                  </td>
                  <td class="py-3 px-4">
                    <span :class="r.is_active ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                      {{ r.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      @click="openEditStaffModal(r)"
                      class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition"
                    >
                      Edit Account
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 4. GOVERNANCE & BILLING TABLE -->
        <div v-if="staffSubTab === 'management'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
          <div class="p-3.5 sm:p-4 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
              <h3 class="font-bold text-slate-900 text-sm">Governance, Administration & Revenue Cycle</h3>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
              {{ managementStaff.length }} Members
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                  <th class="py-3 px-4">Member Name</th>
                  <th class="py-3 px-4">Role</th>
                  <th class="py-3 px-4">Department & Duty</th>
                  <th class="py-3 px-4">Phone</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="m in managementStaff" :key="m.id" class="hover:bg-slate-50/70 transition">
                  <td class="py-3 px-4 font-bold text-slate-900 text-sm">
                    {{ m.name }}
                    <div class="text-slate-400 font-normal text-[11px]">{{ m.email }}</div>
                  </td>
                  <td class="py-3 px-4">
                    <span :class="m.role === 'ADMIN' ? 'bg-purple-100 text-purple-800' : 'bg-amber-100 text-amber-800'" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                      {{ m.role }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <div class="font-semibold text-slate-800">{{ m.department }}</div>
                    <div class="text-slate-400 text-[11px]">{{ m.specialization }}</div>
                  </td>
                  <td class="py-3 px-4 font-mono text-slate-600">
                    {{ m.phone || 'N/A' }}
                  </td>
                  <td class="py-3 px-4">
                    <span :class="m.is_active ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                      {{ m.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      @click="openEditStaffModal(m)"
                      class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition"
                    >
                      Edit Account
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 5: CLINICAL CATALOGS SETUP (MEDICINES & LAB TESTS) -->
      <!-- ============================================================== -->
      <div v-if="activeTab === 'catalogs_setup'" class="space-y-3.5">
        <!-- Sub-Tabs for Catalogs -->
        <div class="bg-white rounded-2xl p-3 border border-slate-200 shadow-xs flex items-center justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-1.5">
            <button
              @click="catalogSubTab = 'medicines'"
              :class="[
                catalogSubTab === 'medicines'
                  ? 'bg-purple-700 text-white font-bold shadow-xs'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200',
                'px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-2'
              ]"
            >
              <span>💊 Medicine Catalog & Formularies</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-purple-900/40 text-purple-100">{{ medicines.length }}</span>
            </button>
            <button
              @click="catalogSubTab = 'lab_tests'"
              :class="[
                catalogSubTab === 'lab_tests'
                  ? 'bg-purple-700 text-white font-bold shadow-xs'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200',
                'px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-2'
              ]"
            >
              <span>🧪 Diagnostic Lab Test Catalog</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-purple-900/40 text-purple-100">{{ labTests.length }}</span>
            </button>
          </div>

          <button
            v-if="catalogSubTab === 'medicines'"
            @click="showAddMedModal = true"
            class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5"
          >
            + Add Medicine to Formulary
          </button>
          <button
            v-else
            @click="showAddLabModal = true"
            class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5"
          >
            + Add Diagnostic Test
          </button>
        </div>

        <!-- MEDICINES TABLE -->
        <div v-if="catalogSubTab === 'medicines'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                  <th class="py-3 px-4">Brand & Generic Name</th>
                  <th class="py-3 px-4">Form & Strength</th>
                  <th class="py-3 px-4">Unit Price</th>
                  <th class="py-3 px-4">Discount</th>
                  <th class="py-3 px-4">Stock Level</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="m in medicines" :key="m.id" class="hover:bg-slate-50/70 transition">
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-900 text-sm">{{ m.brand_name }}</div>
                    <div class="text-slate-400 text-[11px] font-mono">{{ m.generic_name }}</div>
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                      {{ m.dosage_form }} &bull; {{ m.strength }}
                    </span>
                  </td>
                  <td class="py-3 px-4 font-mono font-bold text-slate-900 text-sm">
                    ${{ Number(m.unit_price).toFixed(2) }}
                  </td>
                  <td class="py-3 px-4 font-mono text-slate-500">
                    {{ Number(m.discount_percentage || 0) }}%
                  </td>
                  <td class="py-3 px-4">
                    <span :class="m.stock_quantity < 20 ? 'bg-rose-100 text-rose-800 border-rose-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300'" class="px-2 py-0.5 rounded-full text-[10px] font-bold border">
                      {{ m.stock_quantity }} units
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      @click="openEditMedModal(m)"
                      class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition"
                    >
                      Edit Formulary
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- LAB TESTS TABLE -->
        <div v-if="catalogSubTab === 'lab_tests'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                  <th class="py-3 px-4">Test Code</th>
                  <th class="py-3 px-4">Diagnostic Test Name</th>
                  <th class="py-3 px-4">Specimen Type</th>
                  <th class="py-3 px-4">Standard Price</th>
                  <th class="py-3 px-4">Turnaround Time</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="lt in labTests" :key="lt.id" class="hover:bg-slate-50/70 transition">
                  <td class="py-3 px-4 font-mono font-bold text-purple-700">
                    {{ lt.test_code }}
                  </td>
                  <td class="py-3 px-4 font-bold text-slate-900 text-sm">
                    {{ lt.test_name }}
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                      {{ lt.specimen_type || 'Blood' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 font-mono font-bold text-emerald-700 text-sm">
                    ${{ Number(lt.standard_price).toFixed(2) }}
                  </td>
                  <td class="py-3 px-4 font-mono text-slate-600">
                    {{ lt.turnaround_hours || 12 }} hrs
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      @click="openEditLabModal(lt)"
                      class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition"
                    >
                      Edit Test Setup
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 6: EXECUTIVE OVERVIEW & INTELLIGENCE -->
      <!-- ============================================================== -->
      <div v-if="activeTab === 'overview'" class="space-y-4">
        <!-- 4 Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div @click="activeTab = 'patients'" class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs cursor-pointer hover:border-sky-400 transition">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Patients</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ stats?.total_patients ?? patients.length }}</div>
            <span class="text-xs text-sky-600 font-semibold mt-1 block">View Directory &rarr;</span>
          </div>
          <div @click="activeTab = 'appointments'" class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs cursor-pointer hover:border-teal-400 transition">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Visits Taken</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ stats?.total_appointments ?? appointments.length }}</div>
            <span class="text-xs text-teal-600 font-semibold mt-1 block">View Tokens &rarr;</span>
          </div>
          <div @click="activeTab = 'prescriptions'" class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs cursor-pointer hover:border-emerald-400 transition">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Prescriptions Issued</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ stats?.total_prescriptions ?? prescriptions.length }}</div>
            <span class="text-xs text-emerald-600 font-semibold mt-1 block">View Prescriptions &rarr;</span>
          </div>
          <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Collected Revenue</span>
            <div class="text-2xl font-black text-slate-900 mt-1">${{ Number(stats?.total_revenue || 0).toFixed(2) }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Paid Invoices Ledger</span>
          </div>
        </div>
      </div>

      <!-- ============================================================== -->
      <!-- TAB 7: AUDIT COMPLIANCE -->
      <!-- ============================================================== -->
      <div v-if="activeTab === 'audit_logs'" class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-3.5 sm:p-4 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-900 text-sm">HIPAA Compliance & Immutable Audit Trail</h3>
            <p class="text-[11px] text-slate-500">Chronological access records of PHI reads, patient updates, and clinical sign-offs</p>
          </div>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
            {{ auditLogs.length }} Log Events
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Timestamp</th>
                <th class="py-3 px-4">User Identity</th>
                <th class="py-3 px-4">Action</th>
                <th class="py-3 px-4">Target Entity</th>
                <th class="py-3 px-4">IP Address</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="log in auditLogs" :key="log.id" class="hover:bg-slate-50/70 transition">
                <td class="py-3 px-4 font-mono text-[11px]">
                  {{ formatDateTime(log.created_at) }}
                </td>
                <td class="py-3 px-4">
                  <div class="font-semibold text-slate-900">{{ log.user_name || log.user?.name || 'System' }}</div>
                  <span class="text-[10px] text-slate-400 font-bold uppercase">{{ log.user_role || log.user?.role || 'SYSTEM' }}</span>
                </td>
                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200 font-mono">
                    {{ log.action }}
                  </span>
                </td>
                <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">
                  {{ log.entity_name }} &bull; {{ log.entity_id?.slice(0, 8) }}...
                </td>
                <td class="py-3 px-4 font-mono text-slate-400 text-[11px]">
                  {{ log.ip_address || '127.0.0.1' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: COMPREHENSIVE 5-TAB PATIENT MEDICAL HISTORY DRAWER -->
    <!-- ============================================================== -->
    <div v-if="selectedPatient" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 animate-scaleIn">
        <!-- Patient Banner Header -->
        <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-t-2xl">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-teal-500/20 text-teal-300 font-black text-lg flex items-center justify-center border border-teal-500/40">
              {{ selectedPatient.first_name[0] }}{{ selectedPatient.last_name[0] }}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-lg font-black text-white">{{ selectedPatient.first_name }} {{ selectedPatient.last_name }}</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-teal-400 text-slate-900">
                  {{ selectedPatient.mrn }}
                </span>
              </div>
              <div class="flex items-center gap-2 text-xs text-slate-300 mt-1 flex-wrap">
                <span>{{ selectedPatient.age }} yrs</span>
                <span>&bull;</span>
                <span>{{ selectedPatient.gender }}</span>
                <span>&bull;</span>
                <span class="font-bold text-rose-300">Blood: {{ selectedPatient.blood_group || 'N/A' }}</span>
                <span>&bull;</span>
                <span>Phone: {{ selectedPatient.phone || 'N/A' }}</span>
              </div>
            </div>
          </div>
          <button @click="selectedPatient = null" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center text-xl font-bold">
            &times;
          </button>
        </div>

        <!-- Inner 5-Tab Navigation -->
        <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-1.5 overflow-x-auto">
          <button
            v-for="sub in patientHistoryTabs"
            :key="sub.id"
            @click="activePatientSubTab = sub.id"
            :class="[
              activePatientSubTab === sub.id
                ? 'bg-slate-900 text-white font-bold shadow-xs'
                : 'text-slate-600 hover:bg-slate-200/70',
              'px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 shrink-0'
            ]"
          >
            <span>{{ sub.label }}</span>
            <span :class="activePatientSubTab === sub.id ? 'bg-purple-500 text-white' : 'bg-slate-200 text-slate-700'" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold">
              {{ sub.count }}
            </span>
          </button>
        </div>

        <!-- Drawer Content Stage -->
        <div class="p-5 flex-1 overflow-y-auto space-y-4">
          <!-- SUB-TAB 1: VISITS & APPOINTMENTS -->
          <div v-if="activePatientSubTab === 'appointments'" class="space-y-3">
            <div v-if="!selectedPatient.appointments || selectedPatient.appointments.length === 0" class="p-8 text-center text-slate-400 text-xs">
              No appointments on record for this patient.
            </div>
            <div
              v-for="apt in selectedPatient.appointments"
              :key="apt.id"
              class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between gap-3"
            >
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-extrabold text-teal-700 text-sm">#{{ apt.serial_number }}</span>
                  <span class="text-xs text-slate-500 font-mono">Token: {{ apt.token_number }}</span>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-200 text-slate-800">
                    {{ apt.visit_type === 'REPORT_VISIT' ? 'Report Visit' : 'First Visit' }}
                  </span>
                </div>
                <p class="text-xs font-semibold text-slate-900 mt-1">Dr. {{ apt.provider?.name }} ({{ apt.provider?.specialization }})</p>
                <p class="text-[11px] text-slate-500">{{ formatDate(apt.appointment_date) }} &bull; Fee: ${{ Number(apt.fee_amount || 0).toFixed(2) }} ({{ apt.payment_status }})</p>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                {{ apt.status }}
              </span>
            </div>
          </div>

          <!-- SUB-TAB 2: PRESCRIPTIONS -->
          <div v-if="activePatientSubTab === 'prescriptions'" class="space-y-3">
            <div v-if="!selectedPatient.prescriptions || selectedPatient.prescriptions.length === 0" class="p-8 text-center text-slate-400 text-xs">
              No prescriptions issued yet.
            </div>
            <div
              v-for="rx in selectedPatient.prescriptions"
              :key="rx.id"
              class="p-4 rounded-xl border border-slate-200 bg-white space-y-2"
            >
              <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-2">
                <div>
                  <span class="font-bold text-slate-900">Dr. {{ rx.provider?.name }}</span>
                  <span class="text-slate-400 text-[11px] ml-2">{{ formatDate(rx.created_at) }}</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">{{ rx.status }}</span>
              </div>
              <div class="space-y-1.5 pt-1">
                <div v-for="item in rx.items" :key="item.id" class="flex items-center justify-between text-xs bg-slate-50 p-2 rounded-lg">
                  <span class="font-bold text-slate-800">{{ item.medicine?.brand_name }} ({{ item.medicine?.strength }})</span>
                  <span class="text-slate-500">{{ item.dosage }} &bull; {{ item.frequency }} ({{ item.duration_days }}d)</span>
                </div>
              </div>
              <p v-if="rx.notes" class="text-xs text-slate-500 italic pt-1">{{ rx.notes }}</p>
            </div>
          </div>

          <!-- SUB-TAB 3: CLINICAL SOAP ENCOUNTERS -->
          <div v-if="activePatientSubTab === 'encounters'" class="space-y-3">
            <div v-if="!selectedPatient.encounters || selectedPatient.encounters.length === 0" class="p-8 text-center text-slate-400 text-xs">
              No SOAP notes recorded.
            </div>
            <div v-for="enc in selectedPatient.encounters" :key="enc.id" class="p-4 rounded-xl border border-slate-200 bg-white space-y-2">
              <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-2">
                <span class="font-bold text-slate-900">Encounter with Dr. {{ enc.provider?.name }}</span>
                <span class="text-slate-400">{{ formatDate(enc.encounter_date) }}</span>
              </div>
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2 bg-slate-50 rounded"><span class="font-bold block text-slate-500 uppercase text-[9px]">Subjective</span>{{ enc.subjective || 'N/A' }}</div>
                <div class="p-2 bg-slate-50 rounded"><span class="font-bold block text-slate-500 uppercase text-[9px]">Assessment</span>{{ enc.assessment || 'N/A' }}</div>
                <div class="p-2 bg-slate-50 rounded"><span class="font-bold block text-slate-500 uppercase text-[9px]">Plan</span>{{ enc.plan || 'N/A' }}</div>
                <div class="p-2 bg-slate-50 rounded">
                  <span class="font-bold block text-slate-500 uppercase text-[9px]">Diagnoses</span>
                  <span v-for="diag in enc.diagnoses" :key="diag.id" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 mr-1">
                    {{ diag.icd10_code }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- SUB-TAB 4: LABS -->
          <div v-if="activePatientSubTab === 'labs'" class="space-y-3">
            <div v-if="!selectedPatient.lab_orders || selectedPatient.lab_orders.length === 0" class="p-8 text-center text-slate-400 text-xs">
              No lab orders recorded.
            </div>
            <div v-for="lab in selectedPatient.lab_orders" :key="lab.id" class="p-4 rounded-xl border border-slate-200 bg-white">
              <div class="flex items-center justify-between text-xs mb-2">
                <span class="font-bold text-slate-900">{{ formatDate(lab.created_at) }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">{{ lab.status }}</span>
              </div>
              <div class="space-y-1">
                <div v-for="it in lab.items" :key="it.id" class="text-xs text-slate-700 font-medium">
                  &bull; {{ it.test?.test_name }} ({{ it.test?.test_code }})
                </div>
              </div>
            </div>
          </div>

          <!-- SUB-TAB 5: INVOICES -->
          <div v-if="activePatientSubTab === 'invoices'" class="space-y-3">
            <div v-if="!selectedPatient.invoices || selectedPatient.invoices.length === 0" class="p-8 text-center text-slate-400 text-xs">
              No billing invoices recorded.
            </div>
            <div v-for="inv in selectedPatient.invoices" :key="inv.id" class="p-4 rounded-xl border border-slate-200 bg-white flex items-center justify-between gap-3 text-xs">
              <div>
                <span class="font-bold font-mono text-slate-900">{{ inv.invoice_number }}</span>
                <p class="text-slate-500 text-[11px]">{{ formatDate(inv.created_at) }}</p>
              </div>
              <div class="text-right">
                <div class="font-bold text-emerald-700 text-sm">${{ Number(inv.amount_paid).toFixed(2) }}</div>
                <span class="px-2 py-0.2 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800">{{ inv.payment_status }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="p-4 border-t border-slate-200 flex justify-end">
          <button @click="selectedPatient = null" class="px-5 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
            Close History Drawer
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: CREATE STAFF USER -->
    <div v-if="showAddStaffModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">Create Staff User Account</h3>
          <button @click="showAddStaffModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitStaff" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Full Name *</label>
              <input v-model="staffForm.name" required placeholder="Dr. Sarah Connor" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Email *</label>
              <input v-model="staffForm.email" required type="email" placeholder="sarah@emr.local" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Initial Password *</label>
              <input v-model="staffForm.password" required type="password" placeholder="Password123!" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Role *</label>
              <select v-model="staffForm.role" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white">
                <option value="DOCTOR">Doctor / Physician</option>
                <option value="NURSE">Staff Nurse</option>
                <option value="RECEPTIONIST">Receptionist / Front Desk Cashier</option>
                <option value="BILLER">Diagnostic Billing Specialist</option>
                <option value="ADMIN">System Administrator</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Department</label>
              <input v-model="staffForm.department" placeholder="Medicine, Neurology, Arthrology..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Specialization</label>
              <input v-model="staffForm.specialization" placeholder="Consultant Physician..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div v-if="staffForm.role === 'DOCTOR'" class="p-3 bg-purple-50 rounded-xl border border-purple-200 space-y-2">
            <span class="block font-bold text-purple-900 uppercase tracking-wide text-[10px]">Tiered Consultation Fee Configuration</span>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[10px] font-semibold text-purple-800">New Patient Visit Fee ($) *</label>
                <input v-model.number="staffForm.consultation_fee" required type="number" step="0.01" class="w-full px-3 py-1.5 border border-purple-300 rounded-lg text-sm bg-white" />
              </div>
              <div>
                <label class="block text-[10px] font-semibold text-purple-800">Report Visit Fee ($) *</label>
                <input v-model.number="staffForm.followup_fee" required type="number" step="0.01" class="w-full px-3 py-1.5 border border-purple-300 rounded-lg text-sm bg-white" />
              </div>
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showAddStaffModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs">Cancel</button>
            <button :disabled="staffForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20">
              Create Staff Account
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: EDIT STAFF USER -->
    <div v-if="editingUser" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">Edit Staff Account: {{ editingUser.name }}</h3>
          <button @click="editingUser = null" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitEditStaff" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Full Name</label>
              <input v-model="editStaffForm.name" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Clinical Department</label>
              <input v-model="editStaffForm.department" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Specialization / Role</label>
            <input v-model="editStaffForm.specialization" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
          </div>

          <div v-if="editingUser.role === 'DOCTOR'" class="p-3 bg-purple-50 rounded-xl border border-purple-200 space-y-2">
            <span class="block font-bold text-purple-900 uppercase tracking-wide text-[10px]">Tiered Consultation Fees</span>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[10px] font-semibold text-purple-800">New Patient Visit Fee ($)</label>
                <input v-model.number="editStaffForm.consultation_fee" type="number" step="0.01" class="w-full px-3 py-1.5 border border-purple-300 rounded-lg text-sm bg-white" />
              </div>
              <div>
                <label class="block text-[10px] font-semibold text-purple-800">Report / Follow-up Fee ($)</label>
                <input v-model.number="editStaffForm.followup_fee" type="number" step="0.01" class="w-full px-3 py-1.5 border border-purple-300 rounded-lg text-sm bg-white" />
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input v-model="editStaffForm.is_active" type="checkbox" id="activeToggle" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500" />
            <label for="activeToggle" class="font-semibold text-slate-700 text-xs">Active Profile (Able to access workstations)</label>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="editingUser = null" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs">Cancel</button>
            <button :disabled="editStaffForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20">
              Update Profile
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: ADD MEDICINE -->
    <div v-if="showAddMedModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-lg font-bold text-slate-900">Add Medicine to Formulary</h3>
          <button @click="showAddMedModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitMedicine" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Brand Name *</label>
              <input v-model="medForm.brand_name" required placeholder="e.g. Amoxil" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Generic Name *</label>
              <input v-model="medForm.generic_name" required placeholder="e.g. Amoxicillin" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Dosage Form *</label>
              <select v-model="medForm.dosage_form" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white">
                <option value="Tablet">Tablet</option>
                <option value="Capsule">Capsule</option>
                <option value="Syrup">Syrup</option>
                <option value="Injection">Injection</option>
                <option value="Ointment">Ointment</option>
              </select>
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

    <!-- MODAL: ADD LAB TEST -->
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
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Diagnostic Category</label>
              <input v-model="labForm.category" placeholder="Hematology, Biochemistry..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Test Name *</label>
            <input v-model="labForm.test_name" required placeholder="e.g. Complete Blood Count (CBC) with Differential" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Standard Price ($) *</label>
              <input v-model.number="labForm.standard_price" required type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Specimen</label>
              <input v-model="labForm.specimen_type" placeholder="Blood, Urine, Serum..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Turnaround (Hrs)</label>
              <input v-model.number="labForm.turnaround_hours" type="number" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showAddLabModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs">Cancel</button>
            <button :disabled="labForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20">
              Save Diagnostic Test
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  medicines: Array,
  labTests: Array,
  auditLogs: Array,
  users: Array,
  patients: Array,
  prescriptions: Array,
  appointments: Array,
  stats: Object,
});

// Active Primary Workspace Tab (Default to Patients & History)
const activeTab = ref('patients');

// Sub-Tab Switchers
const staffSubTab = ref('doctors');
const catalogSubTab = ref('medicines');

// Modal States
const showAddStaffModal = ref(false);
const showAddMedModal = ref(false);
const showAddLabModal = ref(false);
const editingUser = ref(null);

// Patient History Modal State
const selectedPatient = ref(null);
const activePatientSubTab = ref('appointments');
const searchPatient = ref('');
const selectedPatientFilter = ref('ALL');

// Prescriptions & Appointments Filters
const searchPrescription = ref('');
const searchAppointment = ref('');

// Primary Workspace Tabs (Clean, not cluttered)
const primaryTabs = computed(() => [
  { id: 'patients', label: '👥 Patients & Histories', count: props.patients?.length || 0 },
  { id: 'prescriptions', label: '💊 Prescriptions Ledger', count: props.prescriptions?.length || 0 },
  { id: 'appointments', label: '📅 Appointments & Tokens', count: props.appointments?.length || 0 },
  { id: 'staff_setup', label: '⚙️ Hospital Staff Setup', count: props.users?.length || 0 },
  { id: 'catalogs_setup', label: '🏥 Clinical Catalogs', count: (props.medicines?.length || 0) + (props.labTests?.length || 0) },
  { id: 'overview', label: '📊 Executive Overview', count: null },
  { id: 'audit_logs', label: '🛡️ Audit Compliance', count: props.auditLogs?.length || 0 },
]);

const patientFilters = [
  { id: 'ALL', label: 'All Patients' },
  { id: 'MULTIPLE', label: 'Multiple Visits (≥ 2)' },
  { id: 'SINGLE', label: 'Single Visit (1)' },
  { id: 'ZERO', label: 'Zero Visits (0)' },
];

const patientHistoryTabs = computed(() => [
  { id: 'appointments', label: 'Visits Taken', count: selectedPatient.value?.appointments?.length || 0 },
  { id: 'prescriptions', label: 'Prescriptions', count: selectedPatient.value?.prescriptions?.length || 0 },
  { id: 'encounters', label: 'Clinical SOAP Notes', count: selectedPatient.value?.encounters?.length || 0 },
  { id: 'labs', label: 'Lab Orders', count: selectedPatient.value?.lab_orders?.length || 0 },
  { id: 'invoices', label: 'Billing Invoices', count: selectedPatient.value?.invoices?.length || 0 },
]);

// Filtered Staff Groups
const doctors = computed(() => (props.users || []).filter(u => u.role === 'DOCTOR'));
const nurses = computed(() => (props.users || []).filter(u => u.role === 'NURSE'));
const receptionists = computed(() => (props.users || []).filter(u => u.role === 'RECEPTIONIST'));
const managementStaff = computed(() => (props.users || []).filter(u => ['ADMIN', 'BILLER'].includes(u.role)));

const doctorDeptFilter = ref('ALL');

const doctorDepartments = computed(() => {
  const depts = new Set((doctors.value || []).map(d => d.department).filter(Boolean));
  return ['ALL', ...Array.from(depts)];
});

const filteredAdminDoctors = computed(() => {
  if (doctorDeptFilter.value === 'ALL') return doctors.value;
  return doctors.value.filter(d => d.department === doctorDeptFilter.value);
});

// Filtered Patients List
const filteredPatients = computed(() => {
  let list = props.patients || [];

  if (selectedPatientFilter.value === 'MULTIPLE') {
    list = list.filter(p => (p.appointments_count || 0) >= 2);
  } else if (selectedPatientFilter.value === 'SINGLE') {
    list = list.filter(p => (p.appointments_count || 0) === 1);
  } else if (selectedPatientFilter.value === 'ZERO') {
    list = list.filter(p => (p.appointments_count || 0) === 0);
  }

  if (searchPatient.value.trim()) {
    const q = searchPatient.value.toLowerCase().trim();
    list = list.filter(p => {
      const name = `${p.first_name || ''} ${p.last_name || ''}`.toLowerCase();
      const mrn = (p.mrn || '').toLowerCase();
      const phone = (p.phone || '').toLowerCase();
      const blood = (p.blood_group || '').toLowerCase();
      return name.includes(q) || mrn.includes(q) || phone.includes(q) || blood.includes(q);
    });
  }

  return list;
});

// Filtered Prescriptions
const filteredPrescriptions = computed(() => {
  let list = props.prescriptions || [];
  if (searchPrescription.value.trim()) {
    const q = searchPrescription.value.toLowerCase().trim();
    list = list.filter(rx => {
      const patientName = `${rx.patient?.first_name || ''} ${rx.patient?.last_name || ''}`.toLowerCase();
      const mrn = (rx.patient?.mrn || '').toLowerCase();
      const doctor = (rx.provider?.name || '').toLowerCase();
      const meds = (rx.items || []).map(it => `${it.medicine?.brand_name || ''} ${it.medicine?.generic_name || ''}`).join(' ').toLowerCase();
      return patientName.includes(q) || mrn.includes(q) || doctor.includes(q) || meds.includes(q);
    });
  }
  return list;
});

// Filtered Appointments
const filteredAppointments = computed(() => {
  let list = props.appointments || [];
  if (searchAppointment.value.trim()) {
    const q = searchAppointment.value.toLowerCase().trim();
    list = list.filter(apt => {
      const patientName = `${apt.patient?.first_name || ''} ${apt.patient?.last_name || ''}`.toLowerCase();
      const mrn = (apt.patient?.mrn || '').toLowerCase();
      const doctor = (apt.provider?.name || '').toLowerCase();
      const token = (apt.token_number || '').toLowerCase();
      const serial = String(apt.serial_number || '');
      return patientName.includes(q) || mrn.includes(q) || doctor.includes(q) || token.includes(q) || serial.includes(q);
    });
  }
  return list;
});

const openPatientHistory = (patient) => {
  selectedPatient.value = patient;
  activePatientSubTab.value = 'appointments';
};

// Forms Setup
const staffForm = useForm({
  name: '',
  email: '',
  password: 'Password123!',
  role: 'DOCTOR',
  department: 'Medicine',
  specialization: 'Internal Medicine Physician',
  consultation_fee: 75.00,
  followup_fee: 30.00,
});

const editStaffForm = useForm({
  name: '',
  role: '',
  department: '',
  specialization: '',
  consultation_fee: 75.00,
  followup_fee: 30.00,
  is_active: true,
});

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

const openEditStaffModal = (user) => {
  editingUser.value = user;
  editStaffForm.name = user.name;
  editStaffForm.role = user.role;
  editStaffForm.department = user.department || '';
  editStaffForm.specialization = user.specialization || '';
  editStaffForm.consultation_fee = user.consultation_fee || 75.00;
  editStaffForm.followup_fee = user.followup_fee || 30.00;
  editStaffForm.is_active = user.is_active ?? true;
};

const submitStaff = () => {
  staffForm.post('/admin/staff', {
    onSuccess: () => {
      showAddStaffModal.value = false;
      staffForm.reset();
    },
  });
};

const submitEditStaff = () => {
  if (!editingUser.value) return;
  editStaffForm.put(`/admin/staff/${editingUser.value.id}`, {
    onSuccess: () => {
      editingUser.value = null;
    },
  });
};

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

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
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
