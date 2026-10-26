<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans select-none overflow-hidden">
    <!-- Header / TV Bar -->
    <header class="bg-slate-900/90 border-b border-slate-800 px-6 py-4 flex items-center justify-between backdrop-blur-md">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-400 flex items-center justify-center shadow-lg shadow-teal-500/20 text-white font-black text-2xl tracking-tight">
          +
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold tracking-tight text-white">Synapse EMR Hospital</h1>
            <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
              Live Patient Display
            </span>
          </div>
          <p class="text-xs text-slate-400">
            Consultation Suite &bull; {{ doctor?.department || 'Outpatient Department' }}
          </p>
        </div>
      </div>

      <!-- Center: Doctor Identity & Room -->
      <div class="hidden md:flex items-center gap-3 bg-slate-800/80 px-4 py-2 rounded-xl border border-slate-700/60">
        <div class="w-9 h-9 rounded-full bg-teal-600/30 text-teal-300 flex items-center justify-center font-bold text-sm border border-teal-500/40">
          MD
        </div>
        <div>
          <p class="text-xs font-semibold text-white">{{ doctor?.name || 'Attending Physician' }}</p>
          <p class="text-[11px] text-teal-400 font-medium">{{ doctor?.specialization || 'General Medicine' }} &bull; Room 302</p>
        </div>
      </div>

      <!-- Right: Realtime Clock & Display Controls -->
      <div class="flex items-center gap-4">
        <div class="text-right">
          <div class="text-2xl font-mono font-bold text-teal-300 tracking-wider">
            {{ currentTime }}
          </div>
          <div class="text-[11px] text-slate-400 font-medium">
            {{ currentDate }}
          </div>
        </div>

        <div class="flex items-center gap-2 pl-2 border-l border-slate-800">
          <!-- Audio Chime Button -->
          <button
            @click="toggleSound"
            :title="soundEnabled ? 'Chime sound enabled' : 'Chime sound muted'"
            class="p-2.5 rounded-xl border transition flex items-center justify-center"
            :class="soundEnabled ? 'bg-teal-500/20 text-teal-300 border-teal-500/40 hover:bg-teal-500/30' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700'"
          >
            <svg v-if="soundEnabled" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
            </svg>
          </button>

          <!-- Fullscreen Toggle -->
          <button
            @click="toggleFullscreen"
            title="Toggle TV Fullscreen"
            class="p-2.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
          </button>

          <!-- Back to Clinical Charting / Staff App -->
          <Link
            href="/doctor/encounters"
            class="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 transition flex items-center gap-1.5"
          >
            <span>Exit TV</span>
          </Link>
        </div>
      </div>
    </header>

    <!-- Main Live Screen Area -->
    <main class="flex-1 p-6 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch overflow-hidden">
      <!-- Left Column: Big "NOW SERVING" Focus Card (7 cols) -->
      <section class="lg:col-span-7 flex flex-col justify-between rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900/90 to-teal-950/40 border-2 border-teal-500/40 p-8 shadow-2xl shadow-teal-950/50 relative overflow-hidden">
        <!-- Ambient Background Glow -->
        <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>

        <div>
          <!-- Header Status Badge -->
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <span class="relative flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500"></span>
              </span>
              <span class="text-sm font-black tracking-widest uppercase text-emerald-400">
                CURRENTLY SERVING IN ROOM
              </span>
            </div>

            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-300">
              Room 302 &bull; Outpatient Wing
            </span>
          </div>

          <!-- If someone is currently serving -->
          <div v-if="currentlyServing" class="space-y-6">
            <div class="flex flex-wrap items-baseline gap-4">
              <div class="inline-block px-5 py-2 rounded-2xl bg-teal-500/20 text-teal-300 border border-teal-500/40 text-xl font-bold font-mono tracking-wider">
                {{ currentlyServing.token_number || ('TK-' + String(currentlyServing.serial_number).padStart(3, '0')) }}
              </div>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider',
                  currentlyServing.visit_type === 'REPORT_VISIT'
                    ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40'
                    : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40'
                ]"
              >
                {{ currentlyServing.visit_type === 'REPORT_VISIT' ? 'Report / Follow-up Visit' : 'First / New Patient Consultation' }}
              </span>
            </div>

            <!-- Massive Serial Number Display -->
            <div class="py-4">
              <div class="text-xs uppercase font-bold tracking-widest text-slate-400 mb-1">
                Token Serial Number
              </div>
              <div class="text-7xl sm:text-8xl md:text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-teal-100 to-teal-400 tracking-tight font-mono">
                #{{ currentlyServing.serial_number }}
              </div>
            </div>

            <!-- Patient Name & Details -->
            <div class="p-6 rounded-2xl bg-slate-800/60 border border-slate-700/80 backdrop-blur-sm">
              <div class="text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                Patient Name
              </div>
              <div class="text-3xl md:text-4xl font-bold text-white tracking-tight">
                {{ currentlyServing.patient?.first_name }} {{ currentlyServing.patient?.last_name }}
              </div>
              <div class="mt-2 flex items-center gap-4 text-xs font-mono text-slate-400">
                <span>MRN: <strong class="text-teal-300">{{ currentlyServing.patient?.mrn }}</strong></span>
                <span>&bull;</span>
                <span>Gender: <strong class="text-slate-300">{{ currentlyServing.patient?.gender }}</strong></span>
              </div>
            </div>
          </div>

          <!-- Empty State: Waiting for next patient -->
          <div v-else class="text-center py-16 px-4">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-teal-400 mb-4 shadow-inner">
              <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Doctor Ready For Next Patient</h3>
            <p class="text-sm text-slate-400 max-w-md mx-auto">
              Please proceed when your serial number is called on this screen or announced over the audio intercom.
            </p>
          </div>
        </div>

        <!-- Room Information Footer -->
        <div class="pt-6 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-teal-400"></span>
            <span>Automated Daily Serial Engine Active</span>
          </div>
          <div>
            Syncing Every 5s &bull; Auto-Refresh Enabled
          </div>
        </div>
      </section>

      <!-- Right Column: "NEXT IN LINE" (5 cols) -->
      <section class="lg:col-span-5 flex flex-col rounded-3xl bg-slate-900 border border-slate-800 p-6 shadow-xl overflow-hidden">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
          <div>
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
              Upcoming Patient Queue
            </h2>
            <p class="text-xs text-slate-400">Please remain seated in the waiting lobby</p>
          </div>
          <div class="px-3 py-1 rounded-full bg-slate-800 text-xs font-bold text-teal-300 border border-slate-700">
            {{ nextInLine?.length || 0 }} In Waiting
          </div>
        </div>

        <!-- Queue List -->
        <div class="flex-1 overflow-y-auto space-y-3 pr-1">
          <div
            v-for="(item, idx) in nextInLine"
            :key="item.id"
            :class="[
              'p-4 rounded-2xl border transition-all flex items-center justify-between',
              idx === 0
                ? 'bg-slate-800/90 border-teal-500/50 shadow-md shadow-teal-950/30'
                : 'bg-slate-850/60 border-slate-800 hover:border-slate-700'
            ]"
          >
            <div class="flex items-center gap-4">
              <!-- Serial Number Pill -->
              <div
                :class="[
                  'w-12 h-12 rounded-xl flex items-center justify-center font-mono font-black text-xl shadow-xs',
                  idx === 0
                    ? 'bg-teal-500 text-slate-950 ring-2 ring-teal-400/50'
                    : 'bg-slate-800 text-teal-300 border border-slate-700'
                ]"
              >
                #{{ item.serial_number }}
              </div>

              <div>
                <div class="flex items-center gap-2">
                  <span class="font-bold text-white text-base">
                    {{ item.patient?.first_name }} {{ item.patient?.last_name }}
                  </span>
                  <span
                    v-if="idx === 0"
                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30"
                  >
                    Next Up
                  </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400 font-mono mt-0.5">
                  <span class="text-teal-400 font-semibold">{{ item.token_number }}</span>
                  <span>&bull;</span>
                  <span>{{ item.patient?.mrn }}</span>
                </div>
              </div>
            </div>

            <!-- Visit Type / Fee Status -->
            <div class="text-right">
              <span
                :class="[
                  'px-2.5 py-1 rounded-md text-[11px] font-semibold block',
                  item.visit_type === 'REPORT_VISIT'
                    ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                    : 'bg-teal-500/10 text-teal-400 border border-teal-500/20'
                ]"
              >
                {{ item.visit_type === 'REPORT_VISIT' ? 'Report Visit' : 'First Visit' }}
              </span>
              <span class="text-[10px] text-slate-500 mt-1 block">
                Paid & Verified
              </span>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="!nextInLine || nextInLine.length === 0" class="h-64 flex flex-col items-center justify-center text-center p-6 text-slate-500">
            <svg class="w-12 h-12 text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="font-medium text-slate-400">Queue is Clear</p>
            <p class="text-xs text-slate-500 mt-1">No checked-in patients currently waiting in line.</p>
          </div>
        </div>

        <!-- Waiting Room Advice Banner -->
        <div class="mt-4 p-3 rounded-xl bg-slate-800/40 border border-slate-800 text-[11px] text-slate-400 flex items-center gap-2">
          <svg class="w-4 h-4 text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Please present your Daily Serial Token at the door when your number appears.</span>
        </div>
      </section>
    </main>

    <!-- Hospital Display Footer Ticker -->
    <footer class="bg-slate-900 border-t border-slate-800 px-6 py-2.5 flex items-center justify-between text-xs text-slate-400">
      <div class="flex items-center gap-2">
        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="font-semibold text-slate-300">Live Hospital OPD System</span>
        <span>&bull;</span>
        <span>Doctor Consultation Fees: New Patient Visit: ${{ Number(doctor?.consultation_fee || 75).toFixed(2) }} | Report / Follow-up: ${{ Number(doctor?.followup_fee || 30).toFixed(2) }}</span>
      </div>
      <div class="font-mono text-slate-500 text-[11px]">
        Synapse EMR v2.4 &bull; Terminal ID: DISP-302
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  doctor: Object,
  currentlyServing: Object,
  nextInLine: Array,
  allQueue: Array,
});

const currentTime = ref('');
const currentDate = ref('');
const soundEnabled = ref(false);
let timer = null;
let clockTimer = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' });
};

// Polite Synthesized Hospital Chime using Web Audio API
const playHospitalChime = () => {
  if (!soundEnabled.value) return;
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const playTone = (freq, delay, duration) => {
      setTimeout(() => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, ctx.currentTime);
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + duration);
      }, delay);
    };
    playTone(523.25, 0, 0.6); // C5
    playTone(659.25, 200, 0.8); // E5
    playTone(783.99, 400, 1.2); // G5
  } catch (e) {
    console.error('Audio chime error', e);
  }
};

const toggleSound = () => {
  soundEnabled.value = !soundEnabled.value;
  if (soundEnabled.value) {
    playHospitalChime();
  }
};

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {});
  } else {
    document.exitFullscreen().catch(() => {});
  }
};

// Play sound whenever currently serving patient changes
watch(() => props.currentlyServing?.id, (newVal, oldVal) => {
  if (newVal && newVal !== oldVal) {
    playHospitalChime();
  }
});

onMounted(() => {
  updateClock();
  clockTimer = setInterval(updateClock, 1000);

  // Poll server every 5 seconds for live queue updates on TV displays
  timer = setInterval(() => {
    router.reload({
      only: ['currentlyServing', 'nextInLine', 'allQueue'],
      preserveScroll: true,
      preserveState: true,
    });
  }, 5000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
  if (clockTimer) clearInterval(clockTimer);
});
</script>
