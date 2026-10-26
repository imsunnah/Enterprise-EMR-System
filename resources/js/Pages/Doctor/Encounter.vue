<template>
  <AuthenticatedLayout>
    <div class="max-w-[1700px] mx-auto space-y-4">
      <!-- Top Clinical Consultation Header & Signing Bar -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link href="/doctor/encounters" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </Link>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-xl font-extrabold text-slate-900">
                Clinical Encounter: {{ encounter.patient?.first_name }} {{ encounter.patient?.last_name }}
              </h2>
              <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-slate-100 text-slate-700">
                {{ encounter.patient?.mrn }}
              </span>
              <span :class="[
                encounter.is_signed ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300',
                'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase border'
              ]">
                {{ encounter.is_signed ? 'Signed & Finalized' : 'In Consultation (Draft)' }}
              </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
              Encounter Date: {{ formatDate(encounter.encounter_date) }} • Provider: {{ $page.props.auth?.user?.name }}
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5 shrink-0 self-end lg:self-auto">
          <template v-if="!encounter.is_signed">
            <button
              @click="saveDraft"
              :disabled="saving"
              class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-1.5"
            >
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
              </svg>
              {{ saving ? 'Saving Draft...' : 'Save Draft' }}
            </button>

            <button
              @click="signEncounter"
              :disabled="signing"
              class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ signing ? 'Signing & Billing...' : 'Sign & Finalize Encounter' }}
            </button>
          </template>

          <template v-else>
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-500">Signed at {{ formatDateTime(encounter.signed_at) }}</span>
              <Link
                v-if="encounter.invoice"
                :href="`/billing/invoice/${encounter.invoice.id}`"
                class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition"
              >
                View Generated Invoice (${{ encounter.invoice.grand_total }}) &rarr;
              </Link>
            </div>
          </template>
        </div>
      </div>

      <!-- Split-Screen Three-Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        <!-- ========================================== -->
        <!-- LEFT COLUMN: Patient Info & Vitals Recorder -->
        <!-- ========================================== -->
        <div class="lg:col-span-3 space-y-4">
          <!-- Patient Summary Card -->
          <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-xl bg-slate-900 text-white font-bold flex items-center justify-center text-lg">
                {{ encounter.patient?.first_name[0] }}{{ encounter.patient?.last_name[0] }}
              </div>
              <div class="min-w-0">
                <h4 class="font-extrabold text-slate-900 text-sm truncate">{{ encounter.patient?.full_name }}</h4>
                <p class="text-xs text-slate-500">{{ encounter.patient?.age }} yrs • {{ encounter.patient?.gender }}</p>
                <p class="text-[11px] font-mono text-slate-400 truncate">{{ encounter.patient?.phone }}</p>
              </div>
            </div>

            <div class="pt-2 border-t border-slate-100 space-y-1.5 text-xs">
              <div class="flex justify-between">
                <span class="text-slate-400">Blood Group:</span>
                <span class="font-bold text-rose-600">{{ encounter.patient?.blood_group || 'Unknown' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Insurance:</span>
                <span class="font-medium text-slate-700 truncate max-w-[140px]">{{ encounter.patient?.insurance_provider || 'Self-pay' }}</span>
              </div>
              <div v-if="encounter.patient?.allergies" class="p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-medium">
                <strong>Allergies:</strong> {{ encounter.patient?.allergies }}
              </div>
            </div>
          </div>

          <!-- Vital Signs Recorder -->
          <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h3 class="font-bold text-slate-900 text-sm">Vital Signs</h3>
              </div>
              <!-- Live BMI badge -->
              <span v-if="calculatedBmi" :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase', bmiStatus.badge]">
                BMI: {{ calculatedBmi }} ({{ bmiStatus.label }})
              </span>
            </div>

            <div class="space-y-3 text-xs">
              <!-- BP (Systolic / Diastolic) -->
              <div>
                <label class="block font-semibold text-slate-600 mb-1">Blood Pressure (mmHg)</label>
                <div class="grid grid-cols-2 gap-2">
                  <div class="relative">
                    <input
                      v-model.number="form.systolic_bp"
                      :disabled="encounter.is_signed"
                      type="number"
                      placeholder="Systolic (120)"
                      class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    />
                    <span class="absolute right-2 top-1.5 text-[10px] text-slate-400">Sys</span>
                  </div>
                  <div class="relative">
                    <input
                      v-model.number="form.diastolic_bp"
                      :disabled="encounter.is_signed"
                      type="number"
                      placeholder="Diastolic (80)"
                      class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    />
                    <span class="absolute right-2 top-1.5 text-[10px] text-slate-400">Dia</span>
                  </div>
                </div>
              </div>

              <!-- Heart Rate & Temperature -->
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block font-semibold text-slate-600 mb-1">Heart Rate (bpm)</label>
                  <input
                    v-model.number="form.heart_rate_bpm"
                    :disabled="encounter.is_signed"
                    type="number"
                    placeholder="72"
                    class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-slate-600 mb-1">Temp (°C)</label>
                  <input
                    v-model.number="form.temperature_c"
                    :disabled="encounter.is_signed"
                    type="number"
                    step="0.1"
                    placeholder="37.0"
                    class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                  />
                </div>
              </div>

              <!-- Oxygen & Height -->
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block font-semibold text-slate-600 mb-1">Oxygen (SpO2 %)</label>
                  <input
                    v-model.number="form.spo2_percentage"
                    :disabled="encounter.is_signed"
                    type="number"
                    step="0.1"
                    placeholder="98"
                    class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label class="block font-semibold text-slate-600 mb-1">Height (cm)</label>
                  <input
                    v-model.number="form.height_cm"
                    :disabled="encounter.is_signed"
                    type="number"
                    step="0.5"
                    placeholder="175"
                    class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                  />
                </div>
              </div>

              <!-- Weight -->
              <div>
                <label class="block font-semibold text-slate-600 mb-1">Weight (kg)</label>
                <input
                  v-model.number="form.weight_kg"
                  :disabled="encounter.is_signed"
                  type="number"
                  step="0.1"
                  placeholder="70"
                  class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                />
              </div>
            </div>
          </div>

          <!-- Past History Drawer -->
          <div v-if="patientHistory && patientHistory.length > 0" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wide pb-2 border-b border-slate-100">
              Previous Encounters ({{ patientHistory.length }})
            </h4>
            <div class="divide-y divide-slate-100 mt-2 space-y-2 text-xs">
              <div v-for="hist in patientHistory" :key="hist.id" class="pt-2">
                <span class="font-bold text-slate-800">{{ formatDate(hist.encounter_date) }}</span>
                <p v-if="hist.diagnoses?.length > 0" class="text-teal-700 font-medium text-[11px] mt-0.5">
                  {{ hist.diagnoses.map(d => d.icd10_code).join(', ') }}
                </p>
                <p class="text-slate-500 line-clamp-2 text-[11px] mt-0.5">{{ hist.assessment_notes || hist.plan_notes }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- CENTER COLUMN: Tabbed SOAP Clinical Editor -->
        <!-- ========================================== -->
        <div class="lg:col-span-5 space-y-4">
          <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4 min-h-[640px] flex flex-col justify-between">
            <div>
              <!-- SOAP Tab Switcher -->
              <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl mb-4">
                <button
                  v-for="s in soapTabs"
                  :key="s.id"
                  @click="activeSoapTab = s.id"
                  :class="[
                    activeSoapTab === s.id
                      ? 'bg-white text-slate-900 shadow-xs font-bold'
                      : 'text-slate-600 hover:text-slate-900',
                    'flex-1 py-1.5 text-xs rounded-lg transition flex items-center justify-center gap-1'
                  ]"
                >
                  <span :class="['w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-extrabold', s.badge]">
                    {{ s.letter }}
                  </span>
                  <span>{{ s.name }}</span>
                </button>
              </div>

              <!-- [S] Subjective -->
              <div v-show="activeSoapTab === 'subjective'" class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Subjective (Chief Complaint & History)</span>
                  <span class="text-[10px] text-slate-400">Patient verbal statements & symptoms</span>
                </div>
                <textarea
                  v-model="form.subjective_notes"
                  :disabled="encounter.is_signed"
                  rows="15"
                  class="w-full p-3.5 border border-slate-300 rounded-xl text-xs font-mono leading-relaxed focus:ring-2 focus:ring-teal-500 focus:outline-none disabled:bg-slate-50"
                  placeholder="Document patient's stated reason for visit, onset, duration, character, aggravating/relieving factors..."
                ></textarea>
              </div>

              <!-- [O] Objective -->
              <div v-show="activeSoapTab === 'objective'" class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Objective (Clinical Examination)</span>
                  <span class="text-[10px] text-slate-400">Physical exam findings & diagnostic telemetry</span>
                </div>
                <textarea
                  v-model="form.objective_notes"
                  :disabled="encounter.is_signed"
                  rows="15"
                  class="w-full p-3.5 border border-slate-300 rounded-xl text-xs font-mono leading-relaxed focus:ring-2 focus:ring-teal-500 focus:outline-none disabled:bg-slate-50"
                  placeholder="General appearance, cardiovascular, pulmonary, abdominal exam findings, auscultations..."
                ></textarea>
              </div>

              <!-- [A] Assessment -->
              <div v-show="activeSoapTab === 'assessment'" class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Assessment (Clinical Impressions)</span>
                  <span class="text-[10px] text-slate-400">Doctor's analysis & condition staging</span>
                </div>
                <textarea
                  v-model="form.assessment_notes"
                  :disabled="encounter.is_signed"
                  rows="15"
                  class="w-full p-3.5 border border-slate-300 rounded-xl text-xs font-mono leading-relaxed focus:ring-2 focus:ring-teal-500 focus:outline-none disabled:bg-slate-50"
                  placeholder="Differential diagnoses, clinical rationale, disease progression or resolution assessment..."
                ></textarea>
              </div>

              <!-- [P] Plan -->
              <div v-show="activeSoapTab === 'plan'" class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Plan (Orders, Rx & Counseling)</span>
                  <span class="text-[10px] text-slate-400">Therapy goals & patient instructions</span>
                </div>
                <textarea
                  v-model="form.plan_notes"
                  :disabled="encounter.is_signed"
                  rows="15"
                  class="w-full p-3.5 border border-slate-300 rounded-xl text-xs font-mono leading-relaxed focus:ring-2 focus:ring-teal-500 focus:outline-none disabled:bg-slate-50"
                  placeholder="Medication dosing adjustments, referrals, lab orders, counseling, and next follow-up interval..."
                ></textarea>
              </div>
            </div>

            <!-- ICD-10 Diagnosis Tagging Section -->
            <div class="pt-4 border-t border-slate-100">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-800 uppercase tracking-wide">ICD-10 Diagnoses Tagging</span>
                <span class="text-[10px] text-slate-400">{{ form.diagnoses.length }} tagged</span>
              </div>

              <!-- Active Tag List -->
              <div class="flex flex-wrap gap-1.5 mb-3">
                <div
                  v-for="(diag, idx) in form.diagnoses"
                  :key="idx"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-200 text-teal-900 text-xs"
                >
                  <span class="font-mono font-bold text-teal-700">[{{ diag.icd10_code }}]</span>
                  <span class="truncate max-w-[150px]">{{ diag.description }}</span>
                  <span class="text-[9px] uppercase px-1 py-0.2 rounded font-bold bg-teal-200/60 text-teal-800">{{ diag.type }}</span>
                  <button
                    v-if="!encounter.is_signed"
                    @click="removeDiagnosis(idx)"
                    class="text-teal-600 hover:text-rose-600 font-bold ml-1"
                  >
                    &times;
                  </button>
                </div>
              </div>

              <!-- Add Diagnosis Form -->
              <div v-if="!encounter.is_signed" class="grid grid-cols-12 gap-2 text-xs">
                <input
                  v-model="newDiag.icd10_code"
                  type="text"
                  placeholder="Code (e.g. I10)"
                  class="col-span-3 px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none uppercase font-mono"
                />
                <input
                  v-model="newDiag.description"
                  type="text"
                  placeholder="Diagnosis Description"
                  class="col-span-5 px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                />
                <select
                  v-model="newDiag.type"
                  class="col-span-2 px-2 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                >
                  <option value="PRIMARY">Primary</option>
                  <option value="SECONDARY">Secondary</option>
                  <option value="CHRONIC">Chronic</option>
                </select>
                <button
                  @click="addDiagnosis"
                  type="button"
                  class="col-span-2 px-2 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold transition"
                >
                  + Add
                </button>
              </div>

              <!-- Quick Common ICD-10 Chips -->
              <div v-if="!encounter.is_signed" class="flex flex-wrap gap-1 mt-2">
                <span class="text-[10px] text-slate-400 self-center mr-1">Common:</span>
                <button
                  v-for="c in commonIcd"
                  :key="c.code"
                  @click="quickAddIcd(c)"
                  type="button"
                  class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition"
                >
                  {{ c.code }} - {{ c.label }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ======================================================= -->
        <!-- RIGHT COLUMN: Catalog-Linked E-Prescriptions & Lab Orders -->
        <!-- ======================================================= -->
        <div class="lg:col-span-4 space-y-4">
          <!-- Catalog Switcher (Medicines vs Lab Orders) -->
          <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl">
              <button
                @click="catalogView = 'prescriptions'"
                :class="[
                  catalogView === 'prescriptions' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900',
                  'flex-1 py-1.5 text-xs rounded-lg transition flex items-center justify-center gap-1.5'
                ]"
              >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span>E-Prescribing (Rx)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ form.prescriptions.length }}</span>
              </button>

              <button
                @click="catalogView = 'lab_orders'"
                :class="[
                  catalogView === 'lab_orders' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900',
                  'flex-1 py-1.5 text-xs rounded-lg transition flex items-center justify-center gap-1.5'
                ]"
              >
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Diagnostic Labs</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">{{ form.lab_orders.length }}</span>
              </button>
            </div>

            <!-- VIEW A: PRESCRIPTIONS FROM MEDICINE CATALOG -->
            <div v-show="catalogView === 'prescriptions'" class="space-y-4">
              <!-- Search & Picker -->
              <div v-if="!encounter.is_signed" class="space-y-2.5">
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wide">
                  Select Medication from Master Catalog
                </label>
                <div class="relative">
                  <input
                    v-model="medSearch"
                    type="text"
                    placeholder="Search brand, generic name..."
                    class="w-full px-3 py-2 pl-8 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                  />
                  <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>

                <!-- Catalog Dropdown List -->
                <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100 text-xs">
                  <div
                    v-for="med in filteredMedicines"
                    :key="med.id"
                    @click="selectMedicine(med)"
                    :class="[
                      selectedMed?.id === med.id ? 'bg-emerald-50 border-l-4 border-emerald-500' : 'hover:bg-slate-50',
                      'p-2.5 cursor-pointer transition flex items-center justify-between'
                    ]"
                  >
                    <div>
                      <div class="font-bold text-slate-900">{{ med.brand_name }} ({{ med.strength }})</div>
                      <div class="text-slate-400 text-[11px]">{{ med.generic_name }} • {{ med.dosage_form }}</div>
                    </div>
                    <div class="text-right">
                      <div class="font-mono font-bold text-slate-800">${{ med.unit_price }}</div>
                      <div v-if="med.discount_percentage > 0" class="text-[10px] text-emerald-600 font-bold">
                        {{ med.discount_percentage }}% off
                      </div>
                      <span :class="[
                        med.stock_quantity > 20 ? 'text-slate-400' : 'text-amber-600 font-bold',
                        'text-[10px]'
                      ]">
                        Stock: {{ med.stock_quantity }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Prescription Item Details Adder -->
                <div v-if="selectedMed" class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2.5">
                  <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-emerald-800">{{ selectedMed.brand_name }} ({{ selectedMed.strength }})</span>
                    <span class="text-slate-400 font-mono">${{ selectedMed.unit_price }}/unit</span>
                  </div>

                  <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                      <label class="block text-[10px] font-semibold text-slate-500">Dosage</label>
                      <input v-model="newRx.dosage" placeholder="1 tablet" class="w-full px-2 py-1 border border-slate-300 rounded text-xs" />
                    </div>
                    <div>
                      <label class="block text-[10px] font-semibold text-slate-500">Frequency</label>
                      <input v-model="newRx.frequency" placeholder="Once daily" class="w-full px-2 py-1 border border-slate-300 rounded text-xs" />
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                      <label class="block text-[10px] font-semibold text-slate-500">Duration (Days)</label>
                      <input v-model.number="newRx.duration_days" type="number" class="w-full px-2 py-1 border border-slate-300 rounded text-xs" />
                    </div>
                    <div>
                      <label class="block text-[10px] font-semibold text-slate-500">Quantity</label>
                      <input v-model.number="newRx.quantity" type="number" class="w-full px-2 py-1 border border-slate-300 rounded text-xs" />
                    </div>
                  </div>

                  <div>
                    <label class="block text-[10px] font-semibold text-slate-500">Instructions</label>
                    <input v-model="newRx.instructions" placeholder="Take after breakfast with water" class="w-full px-2 py-1 border border-slate-300 rounded text-xs" />
                  </div>

                  <!-- Calculated Line Total -->
                  <div class="flex items-center justify-between pt-1 border-t border-slate-200 text-xs">
                    <span class="text-slate-500">Auto-calculated Net Total:</span>
                    <span class="font-mono font-bold text-emerald-700 text-sm">${{ calculatedRxTotal }}</span>
                  </div>

                  <button
                    @click="addPrescriptionItem"
                    type="button"
                    class="w-full py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition"
                  >
                    + Add to Prescription
                  </button>
                </div>
              </div>

              <!-- Current Prescribed Items List -->
              <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">
                  Active Prescriptions ({{ form.prescriptions.length }})
                </span>
                <div v-if="form.prescriptions.length === 0" class="p-6 text-center text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                  No medications prescribed yet.
                </div>
                <div v-else class="space-y-2">
                  <div
                    v-for="(rx, idx) in form.prescriptions"
                    :key="idx"
                    class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs relative flex justify-between items-start"
                  >
                    <div>
                      <div class="font-bold text-slate-900">{{ getMedicineName(rx.medicine_id) }}</div>
                      <div class="text-slate-600 mt-0.5">{{ rx.dosage }} • {{ rx.frequency }} x {{ rx.duration_days }}d</div>
                      <div v-if="rx.instructions" class="text-slate-400 text-[11px] italic mt-0.5">{{ rx.instructions }}</div>
                    </div>
                    <div class="text-right">
                      <span class="font-mono font-bold text-slate-800">Qty: {{ rx.quantity }}</span>
                      <button
                        v-if="!encounter.is_signed"
                        @click="removePrescription(idx)"
                        class="block text-rose-500 hover:text-rose-700 font-bold text-xs mt-1"
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- VIEW B: LAB ORDERS FROM TEST CATALOG -->
            <div v-show="catalogView === 'lab_orders'" class="space-y-4">
              <div v-if="!encounter.is_signed" class="space-y-2.5">
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wide">
                  Select Diagnostic Test from Master Catalog
                </label>
                <div class="relative">
                  <input
                    v-model="labSearch"
                    type="text"
                    placeholder="Search test name, code, category..."
                    class="w-full px-3 py-2 pl-8 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none"
                  />
                  <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>

                <!-- Lab Test Dropdown List -->
                <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100 text-xs">
                  <div
                    v-for="test in filteredLabTests"
                    :key="test.id"
                    @click="addLabOrder(test)"
                    class="p-2.5 hover:bg-sky-50/60 cursor-pointer transition flex items-center justify-between"
                  >
                    <div>
                      <div class="font-bold text-slate-900">{{ test.test_name }}</div>
                      <div class="text-slate-400 text-[11px]">
                        <span class="font-mono font-bold text-sky-700">[{{ test.test_code }}]</span> • {{ test.category }}
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="font-mono font-bold text-slate-800">${{ test.standard_price }}</div>
                      <div v-if="test.discount_percentage > 0" class="text-[10px] text-emerald-600 font-bold">
                        {{ test.discount_percentage }}% off
                      </div>
                      <span class="text-[10px] text-sky-600 font-semibold">+ Order</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Ordered Lab Tests List -->
              <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">
                  Requested Diagnostic Tests ({{ form.lab_orders.length }})
                </span>
                <div v-if="form.lab_orders.length === 0" class="p-6 text-center text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                  No lab tests ordered for this encounter.
                </div>
                <div v-else class="space-y-2">
                  <div
                    v-for="(lo, idx) in form.lab_orders"
                    :key="idx"
                    class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs flex justify-between items-center"
                  >
                    <div>
                      <div class="font-bold text-slate-900">{{ getLabTestName(lo.lab_test_id) }}</div>
                      <div class="flex items-center gap-2 mt-1">
                        <select
                          v-model="lo.priority"
                          :disabled="encounter.is_signed"
                          class="px-2 py-0.5 rounded border border-slate-300 text-[10px] font-bold uppercase"
                        >
                          <option value="ROUTINE">Routine</option>
                          <option value="URGENT">Urgent</option>
                          <option value="STAT">STAT</option>
                        </select>
                      </div>
                    </div>
                    <div class="text-right">
                      <button
                        v-if="!encounter.is_signed"
                        @click="removeLabOrder(idx)"
                        class="text-rose-500 hover:text-rose-700 font-bold text-xs"
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  encounter: Object,
  patientHistory: Array,
  medicines: Array,
  labTests: Array,
});

const activeSoapTab = ref('subjective');
const catalogView = ref('prescriptions');

const soapTabs = [
  { id: 'subjective', letter: 'S', name: 'Subjective', badge: 'bg-teal-100 text-teal-800' },
  { id: 'objective', letter: 'O', name: 'Objective', badge: 'bg-sky-100 text-sky-800' },
  { id: 'assessment', letter: 'A', name: 'Assessment', badge: 'bg-amber-100 text-amber-800' },
  { id: 'plan', letter: 'P', name: 'Plan', badge: 'bg-indigo-100 text-indigo-800' },
];

const saving = ref(false);
const signing = ref(false);

// Reactive Form State initialized with encounter data
const form = ref({
  // Vitals
  temperature_c: props.encounter.temperature_c ?? '',
  heart_rate_bpm: props.encounter.heart_rate_bpm ?? '',
  systolic_bp: props.encounter.systolic_bp ?? '',
  diastolic_bp: props.encounter.diastolic_bp ?? '',
  spo2_percentage: props.encounter.spo2_percentage ?? '',
  weight_kg: props.encounter.weight_kg ?? '',
  height_cm: props.encounter.height_cm ?? '',
  // SOAP
  subjective_notes: props.encounter.subjective_notes || '',
  objective_notes: props.encounter.objective_notes || '',
  assessment_notes: props.encounter.assessment_notes || '',
  plan_notes: props.encounter.plan_notes || '',
  // Diagnoses
  diagnoses: props.encounter.diagnoses ? props.encounter.diagnoses.map(d => ({
    icd10_code: d.icd10_code,
    description: d.description,
    type: d.type,
  })) : [],
  // Prescriptions
  prescriptions: props.encounter.prescriptions?.flatMap(p => p.items.map(it => ({
    medicine_id: it.medicine_id,
    dosage: it.dosage,
    frequency: it.frequency,
    duration_days: it.duration_days,
    quantity: it.quantity,
    instructions: it.instructions,
  }))) || [],
  // Lab orders
  lab_orders: props.encounter.lab_orders?.flatMap(lo => lo.items.map(it => ({
    lab_test_id: it.lab_test_id,
    priority: lo.priority || 'ROUTINE',
  }))) || [],
});

// Live BMI Calculation
const calculatedBmi = computed(() => {
  const w = parseFloat(form.value.weight_kg);
  const h = parseFloat(form.value.height_cm);
  if (w > 0 && h > 0) {
    const hM = h / 100;
    return (w / (hM * hM)).toFixed(1);
  }
  return null;
});

const bmiStatus = computed(() => {
  const bmi = parseFloat(calculatedBmi.value);
  if (!bmi) return { label: '', badge: '' };
  if (bmi < 18.5) return { label: 'Underweight', badge: 'bg-sky-100 text-sky-800' };
  if (bmi < 25.0) return { label: 'Normal', badge: 'bg-emerald-100 text-emerald-800' };
  if (bmi < 30.0) return { label: 'Overweight', badge: 'bg-amber-100 text-amber-800' };
  return { label: 'Obese', badge: 'bg-rose-100 text-rose-800' };
});

// ICD-10 Tagging
const newDiag = ref({
  icd10_code: '',
  description: '',
  type: 'PRIMARY',
});

const commonIcd = [
  { code: 'I10', label: 'Essential Hypertension' },
  { code: 'E11.9', label: 'Type 2 Diabetes Mellitus' },
  { code: 'E78.0', label: 'Hypercholesterolemia' },
  { code: 'J06.9', label: 'Acute Upper Respiratory Infection' },
  { code: 'K21.9', label: 'GERD' },
];

const quickAddIcd = (item) => {
  newDiag.value.icd10_code = item.code;
  newDiag.value.description = item.label;
};

const addDiagnosis = () => {
  if (!newDiag.value.icd10_code || !newDiag.value.description) return;
  form.value.diagnoses.push({ ...newDiag.value });
  newDiag.value.icd10_code = '';
  newDiag.value.description = '';
};

const removeDiagnosis = (idx) => {
  form.value.diagnoses.splice(idx, 1);
};

// Medication Search & Rx Line Item
const medSearch = ref('');
const selectedMed = ref(null);
const newRx = ref({
  dosage: '1 tablet',
  frequency: 'Once daily',
  duration_days: 30,
  quantity: 30,
  instructions: '',
});

const filteredMedicines = computed(() => {
  if (!medSearch.value) return props.medicines || [];
  const q = medSearch.value.toLowerCase();
  return (props.medicines || []).filter(
    m => m.brand_name.toLowerCase().includes(q) || m.generic_name.toLowerCase().includes(q)
  );
});

const selectMedicine = (med) => {
  selectedMed.value = med;
};

const calculatedRxTotal = computed(() => {
  if (!selectedMed.value) return '0.00';
  const unit = parseFloat(selectedMed.value.unit_price);
  const disc = parseFloat(selectedMed.value.discount_percentage || 0);
  const qty = parseInt(newRx.value.quantity) || 1;
  const discountFactor = 1 - (disc / 100);
  return (unit * discountFactor * qty).toFixed(2);
});

const addPrescriptionItem = () => {
  if (!selectedMed.value) return;
  form.value.prescriptions.push({
    medicine_id: selectedMed.value.id,
    dosage: newRx.value.dosage,
    frequency: newRx.value.frequency,
    duration_days: newRx.value.duration_days,
    quantity: newRx.value.quantity,
    instructions: newRx.value.instructions,
  });
  selectedMed.value = null;
};

const removePrescription = (idx) => {
  form.value.prescriptions.splice(idx, 1);
};

const getMedicineName = (medId) => {
  const m = props.medicines?.find(x => x.id === medId);
  return m ? `${m.brand_name} (${m.strength})` : 'Medicine';
};

// Lab Order Search
const labSearch = ref('');
const filteredLabTests = computed(() => {
  if (!labSearch.value) return props.labTests || [];
  const q = labSearch.value.toLowerCase();
  return (props.labTests || []).filter(
    t => t.test_name.toLowerCase().includes(q) || t.test_code.toLowerCase().includes(q)
  );
});

const addLabOrder = (test) => {
  if (form.value.lab_orders.some(o => o.lab_test_id === test.id)) return;
  form.value.lab_orders.push({
    lab_test_id: test.id,
    priority: 'ROUTINE',
  });
};

const removeLabOrder = (idx) => {
  form.value.lab_orders.splice(idx, 1);
};

const getLabTestName = (testId) => {
  const t = props.labTests?.find(x => x.id === testId);
  return t ? `${t.test_name} [${t.test_code}]` : 'Lab Test';
};

// Save Draft & Sign Actions
const saveDraft = () => {
  saving.value = true;
  router.post(`/doctor/encounter/${props.encounter.id}/draft`, form.value, {
    onFinish: () => {
      saving.value = false;
    },
  });
};

const signEncounter = () => {
  if (!confirm('Are you sure you want to sign and finalize this clinical encounter? This will lock the medical notes and generate an automated revenue invoice.')) {
    return;
  }
  signing.value = true;
  // First save any unsaved draft changes, then sign
  router.post(`/doctor/encounter/${props.encounter.id}/draft`, form.value, {
    onSuccess: () => {
      router.post(`/doctor/encounter/${props.encounter.id}/sign`, {}, {
        onFinish: () => {
          signing.value = false;
        },
      });
    },
    onError: () => {
      signing.value = false;
    },
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
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
  });
};
</script>
