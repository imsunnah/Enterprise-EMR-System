<?php

use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PatientAuthController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Clerk\QueueController;
use App\Http\Controllers\Doctor\EncounterController;
use App\Http\Controllers\Patient\PatientPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to role-based workstation
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->role === 'DOCTOR') return redirect()->route('doctor.encounters');
        if ($user->role === 'BILLER') return redirect()->route('billing.index');
        if ($user->role === 'ADMIN') return redirect()->route('admin.catalogs');
        return redirect()->route('clerk.queue');
    }
    if (session()->has('patient_account_id')) {
        return redirect()->route('patient.dashboard');
    }
    return redirect()->route('login');
});

// Staff Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/login/switch-role', [AuthController::class, 'switchRole'])->name('login.switch_role');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Patient Phone Portal Authentication
Route::get('/portal/login', [PatientAuthController::class, 'show'])->name('patient.login');
Route::get('/patient/login', [PatientAuthController::class, 'show'])->name('patient.login.direct');
Route::post('/portal/demo-login', [PatientAuthController::class, 'directDemoLogin'])->name('patient.demo_login');
Route::post('/portal/otp/request', [PatientAuthController::class, 'requestOtp'])->name('patient.otp.request');
Route::post('/portal/otp/verify', [PatientAuthController::class, 'verifyOtp'])->name('patient.otp.verify');
Route::post('/portal/logout', [PatientAuthController::class, 'logout'])->name('patient.logout');

// Patient Portal (Family Household Switcher, Appointments & Medical History)
Route::prefix('portal')->name('patient.')->group(function () {
    Route::get('/dashboard', [PatientPortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/switch-profile', [PatientPortalController::class, 'switchProfile'])->name('switch_profile');
    Route::post('/add-member', [PatientPortalController::class, 'addFamilyMember'])->name('add_member');
    Route::put('/patient/{patient}', [PatientPortalController::class, 'updatePatient'])->name('update_patient');
    Route::post('/book-appointment', [PatientPortalController::class, 'bookAppointment'])->name('book_appointment');
});

// Phase 3: Public / Room Waiting Display Screen (can be mounted on waiting room TV screens)
Route::get('/doctor/display', [EncounterController::class, 'waitingDisplay'])->name('doctor.display');

// Authenticated Staff Routes
Route::middleware(['auth'])->group(function () {

    // Receptionist / Front Desk / Cashier (Queue, Fee Collection & Activation, Walk-Ins)
    Route::middleware(['role:ADMIN,DOCTOR,NURSE,RECEPTIONIST,BILLER'])->prefix('clerk')->name('clerk.')->group(function () {
        Route::get('/queue', [QueueController::class, 'index'])->name('queue');
        Route::post('/register-walkin', [QueueController::class, 'registerWalkIn'])->name('register_walkin');
        Route::post('/collect-fee/{appointment}', [QueueController::class, 'collectFee'])->name('collect_fee');
        Route::post('/check-in/{appointment}', [QueueController::class, 'checkIn'])->name('check_in');
        Route::post('/schedule', [QueueController::class, 'scheduleAppointment'])->name('schedule');
    });

    // Doctor Clinical Encounters & SOAP Charting (Billing Clerks strictly blocked)
    Route::middleware(['role:ADMIN,DOCTOR,NURSE'])->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/encounters', [EncounterController::class, 'index'])->name('encounters');
        Route::post('/appointment/{appointment}/start', [EncounterController::class, 'startFromAppointment'])->name('encounter.start');
        Route::get('/encounter/{encounter}', [EncounterController::class, 'show'])->name('encounter.show');
        Route::post('/encounter/{encounter}/draft', [EncounterController::class, 'saveDraft'])->name('encounter.draft');
        Route::post('/encounter/{encounter}/sign', [EncounterController::class, 'sign'])->name('encounter.sign');
    });

    // Admin Catalogs, Staff Management & Audit Logs
    Route::middleware(['role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/catalogs', [CatalogController::class, 'index'])->name('catalogs');
        Route::post('/catalogs/medicines', [CatalogController::class, 'storeMedicine'])->name('medicines.store');
        Route::put('/catalogs/medicines/{medicine}', [CatalogController::class, 'updateMedicine'])->name('medicines.update');
        Route::post('/catalogs/lab-tests', [CatalogController::class, 'storeLabTest'])->name('lab_tests.store');
        Route::put('/catalogs/lab-tests/{labTest}', [CatalogController::class, 'updateLabTest'])->name('lab_tests.update');
        Route::post('/staff', [CatalogController::class, 'storeStaff'])->name('staff.store');
        Route::put('/staff/{user}', [CatalogController::class, 'updateStaff'])->name('staff.update');
    });

    // Revenue Cycle Management & Diagnostic Billing Counter
    Route::middleware(['role:ADMIN,BILLER'])->prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/diagnostics', [BillingController::class, 'diagnosticDesk'])->name('diagnostics');
        Route::post('/diagnostics/bill', [BillingController::class, 'billDiagnostic'])->name('diagnostics.bill');
        Route::get('/invoice/{invoice}', [BillingController::class, 'show'])->name('invoice.show');
        Route::post('/invoice/{invoice}/pay', [BillingController::class, 'recordPayment'])->name('invoice.pay');
    });
});
