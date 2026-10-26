<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans text-slate-800">
    <!-- Top Demo Quick Switch Bar for Pair Programming & Testing -->
    <header class="bg-slate-900 text-white text-xs border-b border-slate-800 py-1.5 px-4 flex flex-wrap items-center justify-between gap-2 z-50">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-teal-500/20 text-teal-400 font-bold text-[10px]">EMR</span>
        <span class="font-semibold text-slate-200">Enterprise Health Platform</span>
        <span class="text-slate-400 hidden sm:inline">|</span>
        <span class="text-slate-400 hidden md:inline">Autonomous RBAC & Multi-Patient Architecture</span>
      </div>

      <!-- Quick Switch Role Buttons -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <span class="text-slate-400 mr-1 hidden lg:inline">Quick Role Switch:</span>
        <button
          v-for="r in roles"
          :key="r.name"
          @click="switchStaffRole(r.role)"
          :class="[
            currentRole === r.role
              ? 'bg-teal-600 text-white font-semibold ring-1 ring-teal-400'
              : 'bg-slate-800 text-slate-300 hover:bg-slate-700',
            'px-2.5 py-1 rounded text-[11px] transition flex items-center gap-1'
          ]"
        >
          <span :class="['w-1.5 h-1.5 rounded-full', r.dot]"></span>
          {{ r.name }}
        </button>

        <Link
          href="/portal/dashboard"
          :class="[
            isPatientPortal
              ? 'bg-sky-600 text-white font-semibold ring-1 ring-sky-400'
              : 'bg-slate-800 text-slate-300 hover:bg-slate-700',
            'px-2.5 py-1 rounded text-[11px] transition flex items-center gap-1'
          ]"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
          Patient Portal
        </Link>
      </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
      <!-- Main Sidebar -->
      <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between border-r border-slate-800 shrink-0">
        <div>
          <!-- Logo & Brand Header -->
          <div class="p-4 border-b border-slate-800/80 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-teal-500/20 text-white font-bold text-lg">
              +
            </div>
            <div>
              <h1 class="text-base font-bold text-white tracking-tight">Synapse EMR</h1>
              <p class="text-[11px] text-teal-400 font-medium">Enterprise Clinical Suite</p>
            </div>
          </div>

          <!-- Current User Profile Card -->
          <div class="p-4 m-3 rounded-xl bg-slate-800/60 border border-slate-700/50">
            <div class="flex items-center justify-between mb-2">
              <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Active Identity</span>
              <span
                :class="[
                  'px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase',
                  roleBadgeClass
                ]"
              >
                {{ currentRoleLabel }}
              </span>
            </div>
            <p class="font-semibold text-white text-sm truncate">{{ currentUserName }}</p>
            <p class="text-xs text-slate-400 truncate">{{ currentUserSubtext }}</p>
          </div>

          <!-- Navigation Links -->
          <nav class="px-3 py-2 space-y-1">
            <template v-if="!isPatientPortal">
              <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Staff Operations
              </div>

              <!-- Clerk / Reception -->
              <Link
                v-if="['ADMIN', 'DOCTOR', 'NURSE', 'BILLER'].includes(currentRole)"
                href="/clerk/queue"
                :class="[
                  $page.url.startsWith('/clerk')
                    ? 'bg-teal-600/20 text-teal-300 border-l-2 border-teal-500 font-medium'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                  'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition'
                ]"
              >
                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Front Desk & Queue</span>
              </Link>

              <!-- Doctor Clinical Encounters -->
              <Link
                v-if="['ADMIN', 'DOCTOR', 'NURSE'].includes(currentRole)"
                href="/doctor/encounters"
                :class="[
                  $page.url.startsWith('/doctor')
                    ? 'bg-teal-600/20 text-teal-300 border-l-2 border-teal-500 font-medium'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                  'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition'
                ]"
              >
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Clinical SOAP Charting</span>
              </Link>

              <!-- Billing Desk (RCM) -->
              <Link
                v-if="['ADMIN', 'BILLER'].includes(currentRole)"
                href="/billing"
                :class="[
                  $page.url.startsWith('/billing')
                    ? 'bg-teal-600/20 text-teal-300 border-l-2 border-teal-500 font-medium'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                  'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition'
                ]"
              >
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Revenue & Billing Desk</span>
              </Link>

              <!-- Admin Catalogs & Audits -->
              <Link
                v-if="currentRole === 'ADMIN'"
                href="/admin/catalogs"
                :class="[
                  $page.url.startsWith('/admin')
                    ? 'bg-teal-600/20 text-teal-300 border-l-2 border-teal-500 font-medium'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                  'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition'
                ]"
              >
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span>Catalogs & Audit Trail</span>
              </Link>
            </template>

            <!-- Patient Portal Links -->
            <template v-else>
              <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Patient Family Portal
              </div>
              <Link
                href="/portal/dashboard"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium bg-sky-600/20 text-sky-300 border-l-2 border-sky-400"
              >
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Family Medical Hub</span>
              </Link>
            </template>
          </nav>
        </div>

        <!-- Footer / Logout Actions -->
        <div class="p-3 border-t border-slate-800">
          <div v-if="!isPatientPortal" class="flex items-center justify-between">
            <Link
              href="/logout"
              method="post"
              as="button"
              class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Sign Out (Staff)
            </Link>
          </div>
          <div v-else class="flex items-center justify-between">
            <Link
              href="/portal/logout"
              method="post"
              as="button"
              class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Exit Patient Portal
            </Link>
          </div>
        </div>
      </aside>

      <!-- Main Content Stage -->
      <main class="flex-1 overflow-y-auto flex flex-col bg-slate-100/70">
        <!-- Notification Flash Toasts -->
        <div v-if="$page.props.flash?.success" class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 px-6 py-2.5 flex items-center justify-between text-sm shadow-xs animate-fadeIn">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ $page.props.flash.success }}</span>
          </div>
        </div>

        <div v-if="$page.props.flash?.error" class="bg-rose-50 border-b border-rose-200 text-rose-800 px-6 py-2.5 flex items-center justify-between text-sm shadow-xs">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ $page.props.flash.error }}</span>
          </div>
        </div>

        <div v-if="$page.props.flash?.info" class="bg-sky-50 border-b border-sky-200 text-sky-800 px-6 py-2.5 flex items-center justify-between text-sm shadow-xs">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-sky-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ $page.props.flash.info }}</span>
          </div>
        </div>

        <!-- Injected View Slot -->
        <div class="flex-1 p-6 md:p-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();

const roles = [
  { name: 'Doctor', role: 'DOCTOR', dot: 'bg-emerald-400' },
  { name: 'Clerk / Reception', role: 'NURSE', dot: 'bg-cyan-400' },
  { name: 'Billing Clerk', role: 'BILLER', dot: 'bg-amber-400' },
  { name: 'Administrator', role: 'ADMIN', dot: 'bg-purple-400' },
];

const isPatientPortal = computed(() => {
  return page.url.startsWith('/portal');
});

const currentRole = computed(() => {
  if (isPatientPortal.value) return 'PATIENT';
  return page.props.auth?.user?.role || 'GUEST';
});

const currentRoleLabel = computed(() => {
  if (isPatientPortal.value) return 'Patient Portal';
  switch (currentRole.value) {
    case 'DOCTOR': return 'Doctor (MD)';
    case 'NURSE': return 'Staff Nurse';
    case 'BILLER': return 'Billing Clerk';
    case 'ADMIN': return 'Administrator';
    default: return 'User';
  }
});

const currentUserName = computed(() => {
  if (isPatientPortal.value) {
    return page.props.auth?.patientAccount?.phone_number || 'Household Account';
  }
  return page.props.auth?.user?.name || 'Staff Member';
});

const currentUserSubtext = computed(() => {
  if (isPatientPortal.value) {
    const count = page.props.auth?.patientAccount?.patients?.length || 0;
    return `${count} family profile(s) connected`;
  }
  return page.props.auth?.user?.specialization || page.props.auth?.user?.email || 'Clinical Staff';
});

const roleBadgeClass = computed(() => {
  switch (currentRole.value) {
    case 'DOCTOR': return 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40';
    case 'NURSE': return 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40';
    case 'BILLER': return 'bg-amber-500/20 text-amber-300 border border-amber-500/40';
    case 'ADMIN': return 'bg-purple-500/20 text-purple-300 border border-purple-500/40';
    case 'PATIENT': return 'bg-sky-500/20 text-sky-300 border border-sky-500/40';
    default: return 'bg-slate-700 text-slate-300';
  }
});

const switchStaffRole = (role) => {
  router.post('/login/switch-role', { role });
};
</script>
