<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-sky-950 to-slate-950 text-slate-100 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-500 to-teal-400 shadow-xl shadow-sky-500/20 text-white font-black text-3xl mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
      </div>
      <h2 class="text-3xl font-extrabold tracking-tight text-white">Patient Family Portal</h2>
      <p class="mt-2 text-sm text-sky-200">Access and manage medical records for your entire household with one phone number.</p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
      <!-- 2 Direct Login Switcher Tabs -->
      <div class="grid grid-cols-2 p-1 bg-slate-900/90 rounded-2xl border border-slate-700/80 mb-5 shadow-inner">
        <Link
          href="/login"
          class="py-2.5 px-3 text-center text-xs font-bold rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition flex items-center justify-center gap-1.5"
        >
          <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Staff & Doctor Login
        </Link>
        <Link
          href="/patient/login"
          class="py-2.5 px-3 text-center text-xs font-bold rounded-xl bg-gradient-to-r from-sky-500 to-teal-500 text-white shadow-md flex items-center justify-center gap-1.5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          Direct Patient Login
        </Link>
      </div>

      <div class="bg-slate-800/80 backdrop-blur-xl border border-sky-700/40 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
        <!-- Direct 1-Click Patient Portal Access -->
        <div class="mb-6 p-4 rounded-xl bg-sky-950/70 border border-sky-800/80 text-center">
          <p class="text-xs font-semibold uppercase tracking-wider text-sky-300 mb-1">Instant Demo Testing</p>
          <p class="text-xs text-slate-300 mb-3">Jump directly into the Patient Portal without entering an SMS code:</p>
          <button
            @click="directLogin"
            :disabled="directForm.processing"
            type="button"
            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            1-Click Direct Patient Login (+15551234567)
          </button>
        </div>

        <div class="relative flex py-2 items-center mb-4">
          <div class="flex-grow border-t border-slate-700"></div>
          <span class="flex-shrink mx-3 text-[10px] uppercase tracking-wider text-slate-400 font-bold">Or Sign In with Phone OTP</span>
          <div class="flex-grow border-t border-slate-700"></div>
        </div>

        <!-- Step 1: Request OTP -->
        <div v-if="step === 1" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Registered Phone Number</label>
            <input
              v-model="phoneForm.phone_number"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent text-sm"
              placeholder="e.g. +15551234567"
            />
            <span v-if="phoneForm.errors.phone_number" class="text-xs text-rose-400 mt-1 block">{{ phoneForm.errors.phone_number }}</span>
          </div>

          <button
            @click="sendOtp"
            :disabled="phoneForm.processing"
            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-600 hover:to-teal-600 text-white font-semibold text-sm shadow-lg shadow-sky-500/25 transition disabled:opacity-50"
          >
            {{ phoneForm.processing ? 'Sending Code...' : 'Send 6-Digit SMS Code' }}
          </button>
        </div>

        <!-- Step 2: Verify OTP -->
        <div v-else class="space-y-4">
          <div class="text-center p-3 rounded-lg bg-sky-950/60 border border-sky-800 text-xs text-sky-300">
            Code sent to <span class="font-bold text-white">{{ verifyForm.phone_number }}</span>
            <button @click="step = 1" class="ml-2 underline text-teal-400">Change</button>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Enter 6-Digit OTP</label>
            <input
              v-model="verifyForm.otp_code"
              type="text"
              maxlength="6"
              required
              class="mt-1 block w-full tracking-widest text-center text-xl font-mono px-3 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
              placeholder="123456"
            />
            <span v-if="verifyForm.errors.otp_code" class="text-xs text-rose-400 mt-1 block">{{ verifyForm.errors.otp_code }}</span>
            <p class="text-[11px] text-slate-400 mt-1 text-center">Demo Verification Code: <span class="font-mono text-teal-300">123456</span></p>
          </div>

          <button
            @click="verifyOtp"
            :disabled="verifyForm.processing"
            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-teal-500 to-sky-600 hover:from-teal-600 hover:to-sky-700 text-white font-semibold text-sm shadow-lg shadow-teal-500/25 transition disabled:opacity-50"
          >
            {{ verifyForm.processing ? 'Verifying...' : 'Verify & Open Medical Hub' }}
          </button>
        </div>

        <!-- Return to Staff Login -->
        <div class="mt-8 pt-6 border-t border-slate-700/60 text-center">
          <Link
            href="/login"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition"
          >
            &larr; Switch to Staff & Doctor Login
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
  samplePhone: String,
});

const step = ref(1);

const directForm = useForm({
  phone_number: props.samplePhone || '+15551234567',
});

const phoneForm = useForm({
  phone_number: props.samplePhone || '+15551234567',
});

const verifyForm = useForm({
  phone_number: props.samplePhone || '+15551234567',
  otp_code: '123456',
});

const directLogin = () => {
  directForm.post('/portal/demo-login');
};

const sendOtp = () => {
  phoneForm.post('/portal/otp/request', {
    onSuccess: () => {
      verifyForm.phone_number = phoneForm.phone_number;
      step.value = 2;
    },
  });
};

const verifyOtp = () => {
  verifyForm.post('/portal/otp/verify');
};
</script>
