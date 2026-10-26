<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-slate-100 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-400 shadow-xl shadow-teal-500/20 text-white font-black text-3xl mb-4">
        +
      </div>
      <h2 class="text-3xl font-extrabold tracking-tight text-white">Synapse EMR</h2>
      <p class="mt-2 text-sm text-slate-400">Enterprise Electronic Medical Records & Practice Management</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg">
      <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 py-8 px-6 shadow-2xl rounded-2xl sm:px-10">
        <!-- Staff Form -->
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Staff Email Address</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="mt-1 block w-full px-3 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
              placeholder="e.g. doctor@emr.local"
            />
            <span v-if="form.errors.email" class="text-xs text-rose-400 mt-1 block">{{ form.errors.email }}</span>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="mt-1 block w-full px-3 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm"
              placeholder="••••••••"
            />
            <span v-if="form.errors.password" class="text-xs text-rose-400 mt-1 block">{{ form.errors.password }}</span>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full mt-2 py-2.5 px-4 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white font-semibold text-sm shadow-lg shadow-teal-500/25 transition disabled:opacity-50"
          >
            {{ form.processing ? 'Authenticating...' : 'Sign In to Clinical Workstation' }}
          </button>
        </form>

        <!-- Fast Demo One-Click Access -->
        <div class="mt-8 pt-6 border-t border-slate-700/60">
          <p class="text-xs font-semibold uppercase tracking-wider text-teal-400 text-center mb-3">
            Instant Demo Profiles (1-Click Login)
          </p>
          <div class="grid grid-cols-2 gap-2 text-left">
            <button
              v-for="staff in staffAccounts"
              :key="staff.id"
              @click="quickLogin(staff.email)"
              class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-700/80 hover:border-teal-500/60 hover:bg-slate-700/50 transition group flex flex-col justify-between"
            >
              <div class="flex items-center justify-between w-full">
                <span class="text-xs font-bold text-white group-hover:text-teal-300 truncate">{{ staff.name.split(',')[0] }}</span>
                <span :class="['text-[9px] font-bold px-1.5 py-0.5 rounded uppercase', getBadge(staff.role)]">
                  {{ staff.role }}
                </span>
              </div>
              <span class="text-[11px] text-slate-400 truncate mt-1">{{ staff.specialization || staff.email }}</span>
            </button>
          </div>
        </div>

        <!-- Switch to Patient Family Portal -->
        <div class="mt-6 text-center">
          <Link
            href="/portal/login"
            class="inline-flex items-center gap-2 text-xs font-semibold text-sky-400 hover:text-sky-300 transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Patient / Household Portal Login (Phone Auth) &rarr;
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  staffAccounts: Array,
});

const form = useForm({
  email: '',
  password: 'secret123',
  remember: true,
});

const submit = () => {
  form.post('/login');
};

const quickLogin = (email) => {
  form.email = email;
  form.password = 'secret123';
  form.post('/login');
};

const getBadge = (role) => {
  switch (role) {
    case 'DOCTOR': return 'bg-emerald-500/20 text-emerald-300';
    case 'BILLER': return 'bg-amber-500/20 text-amber-300';
    case 'NURSE': return 'bg-cyan-500/20 text-cyan-300';
    case 'ADMIN': return 'bg-purple-500/20 text-purple-300';
    default: return 'bg-slate-700 text-slate-300';
  }
};
</script>
