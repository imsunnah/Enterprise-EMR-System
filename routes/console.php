<?php

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Encounter;
use App\Models\EncounterDiagnosis;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('emr:clear-data', function () {
    $this->warn('Clearing all transactional, clinical, billing, and patient records...');

    Schema::disableForeignKeyConstraints();

    InvoiceItem::truncate();
    Invoice::truncate();
    LabOrderItem::truncate();
    LabOrder::truncate();
    PrescriptionItem::truncate();
    Prescription::truncate();
    EncounterDiagnosis::truncate();
    Encounter::truncate();
    Appointment::truncate();
    Patient::truncate();
    PatientAccount::truncate();

    if (Schema::hasTable('audit_logs')) {
        AuditLog::truncate();
    }

    Schema::enableForeignKeyConstraints();

    $this->info('All patient records, encounters, appointments, and billing invoices cleared successfully!');
    $this->comment('Staff accounts and Medicine/Lab catalogs remain intact for your testing.');
})->purpose('Clear all patients, appointments, encounters, prescriptions, and billing invoices');
