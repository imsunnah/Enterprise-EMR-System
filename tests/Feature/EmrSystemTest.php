<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Encounter;
use App\Models\Invoice;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTestCatalog;
use App\Models\MedicineCatalog;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmrSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_root_redirects_to_login()
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_staff_login_and_role_switching()
    {
        $doctor = User::where('role', 'DOCTOR')->first();

        // 1. Valid login
        $response = $this->post(route('login.submit'), [
            'email' => $doctor->email,
            'password' => 'secret123',
        ]);
        $response->assertRedirect(route('doctor.encounters'));
        $this->assertAuthenticatedAs($doctor);

        // 2. Switch role to Biller
        $response = $this->post(route('login.switch_role'), [
            'role' => 'BILLER',
        ]);
        $response->assertRedirect(route('billing.index'));
        $this->assertEquals('BILLER', auth()->user()->role);

        // 3. Switch role to Receptionist
        $response = $this->post(route('login.switch_role'), [
            'role' => 'RECEPTIONIST',
        ]);
        $response->assertRedirect(route('clerk.queue'));
        $this->assertEquals('RECEPTIONIST', auth()->user()->role);
    }

    public function test_biller_strictly_blocked_from_clinical_soap_notes()
    {
        $biller = User::where('role', 'BILLER')->first();
        $encounter = Encounter::first();

        // Act as Biller
        $this->actingAs($biller);

        // Attempt to view clinical encounter SOAP notes
        $response = $this->get(route('doctor.encounter.show', $encounter->id));

        // Must be blocked with 403 Forbidden per PHI Protection Policy
        $response->assertStatus(403);
    }

    public function test_receptionist_strictly_blocked_from_clinical_soap_notes()
    {
        $receptionist = User::where('role', 'RECEPTIONIST')->first();
        $encounter = Encounter::first();

        $this->actingAs($receptionist);

        // Must be blocked with 403 Forbidden per PHI Protection Policy
        $response = $this->get(route('doctor.encounter.show', $encounter->id));
        $response->assertStatus(403);
    }

    public function test_patient_phone_otp_authentication_and_household_portal()
    {
        // 1. Request OTP
        $response = $this->post(route('patient.otp.request'), [
            'phone_number' => '+15551234567',
        ]);
        $response->assertSessionHas('info');

        // 2. Verify OTP
        $response = $this->post(route('patient.otp.verify'), [
            'phone_number' => '+15551234567',
            'otp_code' => '123456',
        ]);
        $response->assertRedirect(route('patient.dashboard'));

        // 3. View household dashboard
        $account = PatientAccount::where('phone_number', '+15551234567')->first();
        $this->withSession(['patient_account_id' => $account->id]);

        $dashboardResponse = $this->get(route('patient.dashboard'));
        $dashboardResponse->assertStatus(200);

        // 4. Switch family profile
        $child = Patient::where('account_id', $account->id)->where('relationship_to_account_holder', 'CHILD')->first();
        $switchResponse = $this->post(route('patient.switch_profile'), [
            'patient_id' => $child->id,
        ]);
        $switchResponse->assertRedirect(route('patient.dashboard'));
        $this->assertEquals($child->id, session('active_patient_id'));

        // 5. Add family member
        $addResponse = $this->post(route('patient.add_member'), [
            'first_name' => 'Eleanor',
            'last_name' => 'Miller',
            'relationship_to_account_holder' => 'PARENT',
            'date_of_birth' => '1955-04-12',
            'gender' => 'FEMALE',
            'blood_group' => 'A+',
        ]);
        $addResponse->assertRedirect(route('patient.dashboard'));

        $this->assertDatabaseHas('patients', [
            'first_name' => 'Eleanor',
            'relationship_to_account_holder' => 'PARENT',
            'account_id' => $account->id,
        ]);

        // 6. Patient books an online appointment with visit_type = NEW_PATIENT
        $doctor = User::where('role', 'DOCTOR')->first();
        $bookResponse = $this->post(route('patient.book_appointment'), [
            'patient_id' => $child->id,
            'provider_id' => $doctor->id,
            'appointment_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'visit_type' => 'NEW_PATIENT',
            'type' => 'PEDIATRIC_CHECKUP',
            'chief_complaint' => 'Mild seasonal cough and sore throat',
        ]);
        $bookResponse->assertRedirect(route('patient.dashboard'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $child->id,
            'provider_id' => $doctor->id,
            'visit_type' => 'NEW_PATIENT',
            'status' => 'SCHEDULED',
            'payment_status' => 'UNPAID',
        ]);
    }

    public function test_phase1_serial_and_token_generation_and_notification_dispatch()
    {
        $doctor = User::where('role', 'DOCTOR')->first();
        $patient = Patient::first();

        // 1. First Visit / New Patient ($75 standard consultation fee)
        $apt1 = Appointment::create([
            'patient_id' => $patient->id,
            'provider_id' => $doctor->id,
            'appointment_date' => '2026-11-01 09:00:00',
            'visit_type' => 'NEW_PATIENT',
            'type' => 'CONSULTATION',
        ]);

        $this->assertEquals(1, $apt1->serial_number);
        $this->assertEquals('TK-001', $apt1->token_number);
        $this->assertEquals(75.00, $apt1->fee_amount);
        $this->assertEquals('UNPAID', $apt1->payment_status);

        // Check automated notification payload
        $this->assertNotNull($apt1->notification_payload);
        $this->assertStringContainsString('Serial: #1', $apt1->notification_payload['sms_body']);
        $this->assertStringContainsString('TK-001', $apt1->notification_payload['sms_body']);
        $this->assertStringContainsString($doctor->name, $apt1->notification_payload['sms_body']);

        // 2. Second Visit on same day with Report Visit ($30 follow-up fee)
        $apt2 = Appointment::create([
            'patient_id' => $patient->id,
            'provider_id' => $doctor->id,
            'appointment_date' => '2026-11-01 10:00:00',
            'visit_type' => 'REPORT_VISIT',
            'type' => 'FOLLOW_UP',
        ]);

        $this->assertEquals(2, $apt2->serial_number);
        $this->assertEquals('TK-002', $apt2->token_number);
        $this->assertEquals(30.00, $apt2->fee_amount);
    }

    public function test_phase2_front_desk_fee_collection_and_doctor_queue_activation()
    {
        $receptionist = User::where('role', 'RECEPTIONIST')->first();
        $doctor = User::where('role', 'DOCTOR')->first();
        $patient = Patient::first();

        $this->actingAs($receptionist);

        // Scheduled appointment with UNPAID fee
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'provider_id' => $doctor->id,
            'appointment_date' => now()->toDateString() . ' 11:00:00',
            'visit_type' => 'NEW_PATIENT',
            'status' => 'SCHEDULED',
            'payment_status' => 'UNPAID',
        ]);

        $this->assertEquals('UNPAID', $appointment->payment_status);
        $this->assertEquals('SCHEDULED', $appointment->status);

        // Cashier collects fee at front desk
        $response = $this->post(route('clerk.collect_fee', $appointment->id), [
            'payment_method' => 'CASH',
            'payment_reference' => 'REG-00192',
            'notes' => 'Paid at front counter',
        ]);

        $response->assertSessionHas('success');

        // Verify appointment was activated into doctor queue: status = CHECKED_IN, payment_status = PAID
        $appointment->refresh();
        $this->assertEquals('PAID', $appointment->payment_status);
        $this->assertEquals('CHECKED_IN', $appointment->status);
        $this->assertNotNull($appointment->paid_at);

        // Verify official consultation invoice was created
        $this->assertDatabaseHas('invoices', [
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'invoice_type' => 'CONSULTATION',
            'grand_total' => 75.00,
            'status' => 'PAID',
        ]);
    }

    public function test_phase3_doctor_waiting_display_and_queue_search()
    {
        $doctor = User::where('role', 'DOCTOR')->first();
        $this->actingAs($doctor);

        // View Doctor's Queue Desk
        $indexResponse = $this->get(route('doctor.encounters'));
        $indexResponse->assertStatus(200);

        // Search Queue by Serial / Token
        $searchResponse = $this->get(route('doctor.encounters', ['search' => 'TK-001']));
        $searchResponse->assertStatus(200);

        // View Dedicated Fullscreen TV Waiting Room Display Screen
        $displayResponse = $this->get(route('doctor.display'));
        $displayResponse->assertStatus(200);
    }

    public function test_phase4_diagnostic_billing_with_partial_test_selection()
    {
        $biller = User::where('role', 'BILLER')->first();
        $doctor = User::where('role', 'DOCTOR')->first();
        $patient = Patient::first();
        $encounter = Encounter::first();

        // 1. Doctor prescribes 2 tests during consultation
        $test1 = LabTestCatalog::where('test_code', 'CBC-001')->first();
        $test2 = LabTestCatalog::where('test_code', 'HBA1C-003')->first();

        $order = LabOrder::create([
            'patient_id' => $patient->id,
            'provider_id' => $doctor->id,
            'encounter_id' => $encounter->id,
            'status' => 'ORDERED',
        ]);

        $item1 = LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $test1->id,
            'standard_price' => $test1->standard_price,
            'net_price' => $test1->standard_price,
            'billing_status' => 'UNBILLED',
        ]);

        $item2 = LabOrderItem::create([
            'lab_order_id' => $order->id,
            'lab_test_id' => $test2->id,
            'standard_price' => $test2->standard_price,
            'net_price' => $test2->standard_price,
            'billing_status' => 'UNBILLED',
        ]);

        // 2. Billing Clerk accesses Diagnostic Counter Desk
        $this->actingAs($biller);
        $deskResponse = $this->get(route('billing.diagnostics', ['search' => $patient->mrn]));
        $deskResponse->assertStatus(200);

        // 3. Patient decides to conduct ONLY test 1 (partial billing)
        $billResponse = $this->post(route('billing.diagnostics.bill'), [
            'patient_id' => $patient->id,
            'selected_test_ids' => [$item1->id], // Only item 1 selected!
            'payment_method' => 'CREDIT_CARD',
            'payment_reference' => 'CC-AUTH-77291',
            'notes' => 'Patient selected partial testing',
        ]);

        $billResponse->assertRedirect();

        // Verify test 1 is now BILLED
        $item1->refresh();
        $this->assertEquals('BILLED', $item1->billing_status);
        $this->assertNotNull($item1->invoice_id);

        // Verify test 2 remains UNBILLED (flexible itemization preserved)
        $item2->refresh();
        $this->assertEquals('UNBILLED', $item2->billing_status);
        $this->assertNull($item2->invoice_id);

        // Verify Diagnostic Invoice generated with type DIAGNOSTIC
        $invoice = Invoice::find($item1->invoice_id);
        $this->assertNotNull($invoice);
        $this->assertEquals('DIAGNOSTIC', $invoice->invoice_type);
        $this->assertEquals('PAID', $invoice->status);
        $this->assertEquals($test1->standard_price, $invoice->grand_total);
    }

    public function test_admin_staff_management_and_doctor_fees_setup()
    {
        $admin = User::where('role', 'ADMIN')->first();
        $this->actingAs($admin);

        // View Master Setup & Catalogs
        $catResponse = $this->get(route('admin.catalogs'));
        $catResponse->assertStatus(200);

        // Create new Doctor with custom consultation and report visit fees
        $createResponse = $this->post(route('admin.staff.store'), [
            'name' => 'Dr. Robert Oppenheimer, MD',
            'email' => 'oppenheimer@emr.local',
            'password' => 'secret123',
            'role' => 'DOCTOR',
            'department' => 'Oncology',
            'specialization' => 'Consultant Oncologist',
            'license_number' => 'MD-102938',
            'consultation_fee' => 120.00, // Custom first visit fee
            'followup_fee' => 50.00,     // Custom follow-up fee
        ]);

        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'oppenheimer@emr.local',
            'role' => 'DOCTOR',
            'consultation_fee' => 120.00,
            'followup_fee' => 50.00,
        ]);
    }

    public function test_audit_logs_recorded_for_phi_operations()
    {
        $doctor = User::where('role', 'DOCTOR')->first();
        $this->actingAs($doctor);

        // View encounter which triggers PHI view audit
        $encounter = Encounter::first();
        $this->get(route('doctor.encounter.show', $encounter->id));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $doctor->id,
            'action' => 'VIEW_PHI_ENCOUNTER',
            'entity_name' => 'Encounter',
            'entity_id' => $encounter->id,
        ]);
    }
}
