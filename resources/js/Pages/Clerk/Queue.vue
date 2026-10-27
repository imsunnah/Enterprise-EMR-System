<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Top Action Bar -->
      <div class="bg-white rounded-2xl p-6 shadow-xs border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800">Front Desk & Cashier</span>
            <span class="text-xs text-slate-500 font-medium">Outpatient Registration & Serial Dispatch</span>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Reception, Serial Tokens & Queue Activation</h2>
          <p class="text-sm text-slate-500">Collect consultation fees, verify daily serial numbers, issue official invoices, and push patients into active doctor queues.</p>
        </div>

        <div class="flex items-center gap-3 self-start md:self-auto">
          <button
            @click="openScheduleModal"
            class="px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-xs transition"
          >
            + Schedule Appointment
          </button>
          <button
            @click="openWalkInModal"
            class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Register Walk-In Patient
          </button>
        </div>
      </div>

      <!-- Search, Status Filter & Date Selection Bar -->
      <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col lg:flex-row items-center justify-between gap-4">
        <!-- Status Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto w-full lg:w-auto">
          <button
            v-for="st in statusFilters"
            :key="st.id"
            @click="filterStatus(st.id)"
            :class="[
              selectedStatus === st.id
                ? 'bg-slate-900 text-white font-semibold shadow-xs'
                : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200',
              'px-3.5 py-1.5 rounded-xl text-xs transition shrink-0'
            ]"
          >
            {{ st.label }}
          </button>
        </div>

        <!-- Real-Time Search Bar (Phase 2.1) -->
        <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
          <div class="relative w-full sm:w-80">
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Search serial #, token, MRN, phone, name..."
              class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200 shrink-0">
            {{ appointments.length }} In Queue
          </span>
        </div>
      </div>

      <!-- Queue Table -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="font-bold text-slate-900 text-sm">Patient Arrival, Daily Serials & Invoicing Queue</h3>
          <span class="text-xs text-slate-400">Phase 1 & Phase 2 Active</span>
        </div>

        <div v-if="appointments.length === 0" class="p-12 text-center text-slate-400 text-sm">
          No patients found in queue matching current criteria.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Daily Token / Serial</th>
                <th class="py-3 px-4">Patient Information</th>
                <th class="py-3 px-4">Visit Type & Fee</th>
                <th class="py-3 px-4">Doctor & Room</th>
                <th class="py-3 px-4">Payment Status</th>
                <th class="py-3 px-4">Queue State</th>
                <th class="py-3 px-4 text-right">Cashier Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="apt in appointments" :key="apt.id" class="hover:bg-slate-50/70 transition">
                <!-- Daily Token & Serial -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-teal-300 font-mono font-black text-sm flex items-center justify-center shrink-0">
                      #{{ apt.serial_number || '-' }}
                    </div>
                    <div>
                      <span class="font-mono font-bold text-teal-800 text-xs block">
                        {{ apt.token_number || ('TK-' + String(apt.serial_number).padStart(3, '0')) }}
                      </span>
                      <span class="text-[10px] text-slate-400">Daily Serial</span>
                    </div>
                  </div>
                </td>

                <!-- Patient Info -->
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900 text-sm">
                    {{ apt.patient?.first_name }} {{ apt.patient?.last_name }}
                  </div>
                  <div class="text-slate-500 text-[11px] font-mono">
                    MRN: <strong class="text-slate-700">{{ apt.patient?.mrn }}</strong> &bull; {{ apt.patient?.phone }}
                  </div>
                </td>

                <!-- Visit Type & Fee Categorization -->
                <td class="py-3.5 px-4">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase inline-block',
                      apt.visit_type === 'REPORT_VISIT'
                        ? 'bg-amber-100 text-amber-800 border border-amber-300'
                        : 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                    ]"
                  >
                    {{ apt.visit_type === 'REPORT_VISIT' ? 'Report / Follow-up' : 'First / New Patient' }}
                  </span>
                  <div class="text-xs font-bold text-slate-900 mt-1">
                    ${{ Number(apt.fee_amount || 0).toFixed(2) }}
                  </div>
                </td>

                <!-- Assigned Doctor -->
                <td class="py-3.5 px-4">
                  <span class="font-bold text-slate-800 block">{{ apt.provider?.name }}</span>
                  <span class="text-slate-400 text-[11px]">{{ apt.provider?.specialization }} &bull; OPD</span>
                </td>

                <!-- Payment Status -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-1.5">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide',
                        apt.payment_status === 'PAID'
                          ? 'bg-emerald-100 text-emerald-800'
                          : 'bg-rose-100 text-rose-800'
                      ]"
                    >
                      {{ apt.payment_status }}
                    </span>
                  </div>
                  <span v-if="apt.paid_at" class="text-[10px] text-slate-400 mt-0.5 block">
                    Paid at {{ formatTime(apt.paid_at) }}
                  </span>
                </td>

                <!-- Queue State -->
                <td class="py-3.5 px-4">
                  <span :class="[
                    getStatusBadge(apt.status),
                    'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide'
                  ]">
                    {{ apt.status }}
                  </span>
                </td>

                <!-- Actions: Collect Fee, Invoice, Check In -->
                <td class="py-3.5 px-4 text-right">
                  <!-- Case 1: Unpaid -> Reception collects fee to activate queue -->
                  <div v-if="apt.payment_status === 'UNPAID'" class="flex items-center justify-end gap-1.5">
                    <button
                      @click="openCollectFeeModal(apt)"
                      class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      Collect ${{ Number(apt.fee_amount || 0).toFixed(0) }} & Activate
                    </button>
                  </div>

                  <!-- Case 2: Paid -> Official Invoice view + In Waiting Area -->
                  <div v-else class="flex items-center justify-end gap-2">
                    <button
                      v-if="apt.invoice"
                      @click="viewInvoice(apt.invoice)"
                      class="px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition flex items-center gap-1"
                    >
                      <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                      </svg>
                      Print Receipt
                    </button>
                    <span v-if="apt.status === 'CHECKED_IN'" class="text-xs text-teal-700 font-semibold flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                      In Doctor Queue
                    </span>
                    <span v-else-if="apt.status === 'IN_PROGRESS'" class="text-xs text-purple-700 font-semibold">
                      In Consultation
                    </span>
                    <span v-else-if="apt.status === 'COMPLETED'" class="text-xs text-emerald-700 font-semibold">
                      Finished
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal: Phase 2 Front Desk Fee Collection & Doctor Queue Activation -->
    <div v-if="collectingAppointment" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-teal-100 text-teal-800">
              Phase 2: Payment & Queue Activation
            </span>
            <h3 class="text-lg font-bold text-slate-900 mt-1">Collect Doctor Consultation Fee</h3>
          </div>
          <button @click="collectingAppointment = null" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <div class="my-4 p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-slate-500">Patient:</span>
            <span class="font-bold text-slate-900">{{ collectingAppointment.patient?.first_name }} {{ collectingAppointment.patient?.last_name }} ({{ collectingAppointment.patient?.mrn }})</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Doctor:</span>
            <span class="font-bold text-slate-800">{{ collectingAppointment.provider?.name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Daily Serial & Token:</span>
            <span class="font-mono font-bold text-teal-700">Serial #{{ collectingAppointment.serial_number }} &bull; {{ collectingAppointment.token_number }}</span>
          </div>
          <div class="flex justify-between items-center pt-2 border-t border-slate-200">
            <div>
              <span class="text-slate-500">Visit Category:</span>
              <span class="ml-2 font-bold text-xs uppercase" :class="collectingAppointment.visit_type === 'REPORT_VISIT' ? 'text-amber-700' : 'text-emerald-700'">
                {{ collectingAppointment.visit_type === 'REPORT_VISIT' ? 'Report Visit / Follow-up' : 'New Patient / First Visit' }}
              </span>
            </div>
            <div class="text-lg font-black text-slate-900">
              ${{ Number(collectingAppointment.fee_amount || 0).toFixed(2) }}
            </div>
          </div>
        </div>

        <form @submit.prevent="submitFeeCollection" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Payment Method *</label>
            <div class="grid grid-cols-3 gap-2">
              <label
                v-for="pm in ['CASH', 'CREDIT_CARD', 'MOBILE_BANKING']"
                :key="pm"
                :class="[
                  feeForm.payment_method === pm
                    ? 'bg-teal-50 border-teal-500 text-teal-900 font-bold ring-2 ring-teal-400/20'
                    : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50',
                  'p-2.5 rounded-xl border text-center cursor-pointer transition'
                ]"
              >
                <input type="radio" v-model="feeForm.payment_method" :value="pm" class="sr-only" />
                <span>{{ pm.replace('_', ' ') }}</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Transaction / Receipt Reference (Optional)</label>
            <input
              v-model="feeForm.payment_reference"
              type="text"
              placeholder="e.g. POS-98432 or Cash Register Slip"
              class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
            />
          </div>

          <div class="p-3 rounded-xl bg-teal-50/70 border border-teal-200 text-[11px] text-teal-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Confirming payment will automatically generate an official invoice and push this patient into the Doctor's live room display queue.</span>
          </div>

          <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
            <button @click="collectingAppointment = null" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button
              :disabled="feeForm.processing"
              type="submit"
              class="px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition"
            >
              {{ feeForm.processing ? 'Processing Payment...' : 'Confirm Payment & Activate Queue' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Fast Walk-In Patient Registration & Fee -->
    <div v-if="showWalkInModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Walk-In Patient Registration & Token Dispatch</h3>
            <p class="text-xs text-slate-500">Auto-generates MRN, Daily Serial, and Visit Fee categorization</p>
          </div>
          <button @click="showWalkInModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitWalkIn" class="mt-4 space-y-4 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">First Name *</label>
              <input v-model="walkInForm.first_name" required type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Last Name *</label>
              <input v-model="walkInForm.last_name" required type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Phone Number *</label>
              <input v-model="walkInForm.phone" required type="text" placeholder="+15550000000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Date of Birth * (Max Today)</label>
              <input v-model="walkInForm.date_of_birth" :max="todayDate" required type="date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Gender *</label>
              <select v-model="walkInForm.gender" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                <option value="MALE">Male</option>
                <option value="FEMALE">Female</option>
                <option value="OTHER">Other</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Blood Group (Fixed)</label>
              <select v-model="walkInForm.blood_group" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                <option value="">Select Blood Group...</option>
                <option v-for="bg in bloodGroups" :key="bg" :value="bg">{{ bg }}</option>
              </select>
            </div>
          </div>

          <!-- Doctor Assignment with Department Filter -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px]">Assign Attending Physician *</label>
              <div class="flex items-center gap-1">
                <span class="text-[10px] text-slate-400 font-bold uppercase">Dept:</span>
                <select v-model="walkInDept" class="px-2 py-0.5 border border-slate-300 rounded text-[11px] bg-slate-50 focus:bg-white focus:outline-none">
                  <option v-for="dept in availableDoctorDepartments" :key="dept" :value="dept">
                    {{ dept === 'ALL' ? 'All Departments' : dept }}
                  </option>
                </select>
              </div>
            </div>
            <select v-model="walkInForm.provider_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
              <option value="" disabled>Select Doctor...</option>
              <option v-for="doc in filteredWalkInDoctors" :key="doc.id" :value="doc.id">
                {{ doc.name }} &bull; {{ doc.department }} ({{ doc.specialization }}) &bull; First Visit: ${{ Number(doc.consultation_fee || 75).toFixed(0) }} | Report: ${{ Number(doc.followup_fee || 30).toFixed(0) }}
              </option>
            </select>
          </div>

          <!-- Phase 1: Visit Categorization (New Patient vs Report Visit) -->
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <span class="block font-bold text-slate-800 uppercase tracking-wide text-[10px] mb-2">Visit Fee Categorization *</span>
            <div class="grid grid-cols-2 gap-3">
              <label
                :class="[
                  walkInForm.visit_type === 'NEW_PATIENT'
                    ? 'bg-emerald-50 border-emerald-500 text-emerald-900 font-bold ring-2 ring-emerald-400/20'
                    : 'bg-white border-slate-200 text-slate-700',
                  'p-3 rounded-xl border cursor-pointer transition'
                ]"
              >
                <input type="radio" v-model="walkInForm.visit_type" value="NEW_PATIENT" class="sr-only" />
                <div class="text-xs">First Visit / New Patient</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Standard Doctor Consultation Fee (${{ selectedDoctorStandardFee }})</div>
              </label>

              <label
                :class="[
                  walkInForm.visit_type === 'REPORT_VISIT'
                    ? 'bg-amber-50 border-amber-500 text-amber-900 font-bold ring-2 ring-amber-400/20'
                    : 'bg-white border-slate-200 text-slate-700',
                  'p-3 rounded-xl border cursor-pointer transition'
                ]"
              >
                <input type="radio" v-model="walkInForm.visit_type" value="REPORT_VISIT" class="sr-only" />
                <div class="text-xs">Report Visit / Follow-up</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Discounted Follow-up Fee (${{ selectedDoctorFollowupFee }})</div>
              </label>
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Chief Complaint</label>
            <input v-model="walkInForm.chief_complaint" placeholder="e.g. Fever, chest pain, routine review..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
          </div>

          <!-- Collect Fee Now Option -->
          <div class="p-3 bg-teal-50/60 rounded-xl border border-teal-200 space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="walkInForm.collect_fee_now" type="checkbox" class="w-4 h-4 text-teal-600 rounded border-slate-300" />
              <span class="font-bold text-teal-900">Collect Fee Now & Push Immediately to Doctor's Queue</span>
            </label>
            <div v-if="walkInForm.collect_fee_now" class="grid grid-cols-2 gap-2 pt-2">
              <div>
                <label class="block text-[10px] text-slate-600">Payment Method</label>
                <select v-model="walkInForm.payment_method" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
                  <option value="CASH">Cash</option>
                  <option value="CREDIT_CARD">Credit Card</option>
                  <option value="MOBILE_BANKING">Mobile Banking</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] text-slate-600">Reference / Note</label>
                <input v-model="walkInForm.payment_reference" placeholder="Optional Slip #" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white" />
              </div>
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showWalkInModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button :disabled="walkInForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition">
              {{ walkInForm.processing ? 'Generating Token...' : 'Register & Assign Token' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Schedule Appointment (Phase 1) -->
    <div v-if="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Schedule Patient Appointment</h3>
            <p class="text-xs text-slate-500">Assigns daily serial token & dispatches automated SMS/Email</p>
          </div>
          <button @click="showScheduleModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitSchedule" class="mt-4 space-y-4 text-xs">
          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Select Patient *</label>
            <select v-model="scheduleForm.patient_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
              <option value="" disabled>Choose existing patient...</option>
              <option v-for="pt in recentPatients" :key="pt.id" :value="pt.id">
                {{ pt.first_name }} {{ pt.last_name }} ({{ pt.mrn }}) - {{ pt.phone }}
              </option>
            </select>
          </div>

          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px]">Attending Doctor *</label>
              <div class="flex items-center gap-1">
                <span class="text-[10px] text-slate-400 font-bold uppercase">Dept:</span>
                <select v-model="scheduleDept" class="px-2 py-0.5 border border-slate-300 rounded text-[11px] bg-slate-50 focus:bg-white focus:outline-none">
                  <option v-for="dept in availableDoctorDepartments" :key="dept" :value="dept">
                    {{ dept === 'ALL' ? 'All Departments' : dept }}
                  </option>
                </select>
              </div>
            </div>
            <select v-model="scheduleForm.provider_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
              <option value="" disabled>Choose physician...</option>
              <option v-for="doc in filteredScheduleDoctors" :key="doc.id" :value="doc.id">
                {{ doc.name }} &bull; {{ doc.department }} ({{ doc.specialization }}) &bull; Consultation: ${{ Number(doc.consultation_fee || 75).toFixed(0) }} | Report: ${{ Number(doc.followup_fee || 30).toFixed(0) }}
              </option>
            </select>
          </div>

          <!-- Phase 1: Visit Type Selection -->
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <span class="block font-bold text-slate-800 uppercase tracking-wide text-[10px] mb-2">Visit Fee Type *</span>
            <div class="grid grid-cols-2 gap-2">
              <label
                :class="[
                  scheduleForm.visit_type === 'NEW_PATIENT'
                    ? 'bg-emerald-50 border-emerald-500 text-emerald-900 font-bold'
                    : 'bg-white border-slate-200 text-slate-700',
                  'p-2.5 rounded-xl border cursor-pointer text-center transition'
                ]"
              >
                <input type="radio" v-model="scheduleForm.visit_type" value="NEW_PATIENT" class="sr-only" />
                <span>First Visit</span>
              </label>

              <label
                :class="[
                  scheduleForm.visit_type === 'REPORT_VISIT'
                    ? 'bg-amber-50 border-amber-500 text-amber-900 font-bold'
                    : 'bg-white border-slate-200 text-slate-700',
                  'p-2.5 rounded-xl border cursor-pointer text-center transition'
                ]"
              >
                <input type="radio" v-model="scheduleForm.visit_type" value="REPORT_VISIT" class="sr-only" />
                <span>Report / Follow-up</span>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Date & Time *</label>
              <input v-model="scheduleForm.appointment_date" required type="datetime-local" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Appointment Type *</label>
              <select v-model="scheduleForm.type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="CONSULTATION">Consultation</option>
                <option value="ROUTINE_CHECKUP">Routine Checkup</option>
                <option value="FOLLOW_UP">Follow-Up</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-700 uppercase tracking-wide text-[10px] mb-1">Chief Complaint</label>
            <input v-model="scheduleForm.chief_complaint" placeholder="Brief reason for appointment..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showScheduleModal = false" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
              Cancel
            </button>
            <button :disabled="scheduleForm.processing" type="submit" class="px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition">
              {{ scheduleForm.processing ? 'Booking...' : 'Book & Issue Serial Token' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Official Invoice Receipt Modal (Phase 2 Print View) -->
    <div v-if="previewInvoice" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-scaleIn">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <h3 class="text-base font-bold text-slate-900">Official Consultation Invoice</h3>
          </div>
          <button @click="previewInvoice = null" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <div class="py-4 space-y-3 text-xs">
          <div class="text-center pb-3 border-b border-slate-100">
            <h4 class="font-bold text-slate-900 text-sm">Synapse EMR Hospital</h4>
            <p class="text-slate-500 text-[11px]">Official Outpatient Consultation Receipt</p>
            <span class="font-mono font-bold text-teal-700 text-xs mt-1 block">Invoice: {{ previewInvoice.invoice_number }}</span>
          </div>

          <div class="flex justify-between">
            <span class="text-slate-500">Status:</span>
            <span class="font-bold text-emerald-700 uppercase">{{ previewInvoice.status }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Grand Total:</span>
            <span class="font-bold text-slate-900">${{ Number(previewInvoice.grand_total).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Amount Paid:</span>
            <span class="font-bold text-emerald-600">${{ Number(previewInvoice.amount_paid).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Balance Due:</span>
            <span class="font-bold text-slate-700">${{ Number(previewInvoice.balance_due).toFixed(2) }}</span>
          </div>
        </div>

        <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
          <button @click="previewInvoice = null" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs">
            Close
          </button>
          <button @click="printReceipt" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Slip
          </button>
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
  appointments: Array,
  doctors: Array,
  recentPatients: Array,
  selectedStatus: String,
  selectedDate: String,
  searchTerm: String,
});

const showWalkInModal = ref(false);
const showScheduleModal = ref(false);
const collectingAppointment = ref(null);
const previewInvoice = ref(null);
const searchQuery = ref(props.searchTerm || '');
let searchTimer = null;

const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'UNKNOWN'];
const todayDate = computed(() => new Date().toISOString().split('T')[0]);

const statusFilters = [
  { id: 'ALL', label: 'All Queue' },
  { id: 'SCHEDULED', label: 'Unpaid / Scheduled' },
  { id: 'CHECKED_IN', label: 'Paid & Waiting' },
  { id: 'IN_PROGRESS', label: 'In Consultation' },
  { id: 'COMPLETED', label: 'Completed' },
];

const walkInDept = ref('ALL');
const scheduleDept = ref('ALL');

const availableDoctorDepartments = computed(() => {
  const depts = new Set((props.doctors || []).map(d => d.department).filter(Boolean));
  return ['ALL', ...Array.from(depts)];
});

const filteredWalkInDoctors = computed(() => {
  if (walkInDept.value === 'ALL') return props.doctors || [];
  return (props.doctors || []).filter(d => d.department === walkInDept.value);
});

const filteredScheduleDoctors = computed(() => {
  if (scheduleDept.value === 'ALL') return props.doctors || [];
  return (props.doctors || []).filter(d => d.department === scheduleDept.value);
});

const walkInForm = useForm({
  first_name: '',
  last_name: '',
  phone: '',
  date_of_birth: '',
  gender: 'MALE',
  blood_group: '',
  provider_id: '',
  visit_type: 'NEW_PATIENT',
  chief_complaint: '',
  collect_fee_now: true,
  payment_method: 'CASH',
  payment_reference: '',
});

const scheduleForm = useForm({
  patient_id: '',
  provider_id: '',
  appointment_date: '',
  visit_type: 'NEW_PATIENT',
  type: 'CONSULTATION',
  chief_complaint: '',
});

const feeForm = useForm({
  payment_method: 'CASH',
  payment_reference: '',
  notes: 'Front Desk Consultation Fee Collection',
});

const selectedDoctorStandardFee = computed(() => {
  const doc = props.doctors?.find(d => d.id === walkInForm.provider_id);
  return Number(doc?.consultation_fee || 75).toFixed(0);
});

const selectedDoctorFollowupFee = computed(() => {
  const doc = props.doctors?.find(d => d.id === walkInForm.provider_id);
  return Number(doc?.followup_fee || 30).toFixed(0);
});

const handleSearch = () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    router.get('/clerk/queue', {
      search: searchQuery.value,
      status: props.selectedStatus,
    }, {
      preserveState: true,
      replace: true,
    });
  }, 300);
};

const filterStatus = (status) => {
  router.get('/clerk/queue', {
    status,
    search: searchQuery.value,
  }, { preserveState: true });
};

const openCollectFeeModal = (apt) => {
  collectingAppointment.value = apt;
  feeForm.reset();
  feeForm.payment_method = 'CASH';
};

const submitFeeCollection = () => {
  if (!collectingAppointment.value) return;
  feeForm.post(`/clerk/collect-fee/${collectingAppointment.value.id}`, {
    onSuccess: () => {
      collectingAppointment.value = null;
    },
  });
};

const openWalkInModal = () => {
  if (props.doctors?.length > 0) {
    walkInForm.provider_id = props.doctors[0].id;
  }
  showWalkInModal.value = true;
};

const openScheduleModal = () => {
  if (props.doctors?.length > 0) {
    scheduleForm.provider_id = props.doctors[0].id;
  }
  showScheduleModal.value = true;
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

const viewInvoice = (invoice) => {
  previewInvoice.value = invoice;
};

const printReceipt = () => {
  window.print();
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'CHECKED_IN': return 'bg-cyan-100 text-cyan-800';
    case 'IN_PROGRESS': return 'bg-purple-100 text-purple-800';
    case 'SCHEDULED': return 'bg-slate-100 text-slate-700';
    case 'COMPLETED': return 'bg-emerald-100 text-emerald-800';
    default: return 'bg-slate-100 text-slate-600';
  }
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};
</script>
