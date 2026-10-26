<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Encounter;
use App\Models\Invoice;
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
        $child = Patient::where('relationship_to_account_holder', 'CHILD')->first();
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

        // 6. Patient books an online appointment for themselves
        $doctor = User::where('role', 'DOCTOR')->first();
        $bookResponse = $this->post(route('patient.book_appointment'), [
            'patient_id' => $child->id,
            'provider_id' => $doctor->id,
            'appointment_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'type' => 'PEDIATRIC_CHECKUP',
            'chief_complaint' => 'Mild seasonal cough and sore throat',
        ]);
        $bookResponse->assertRedirect(route('patient.dashboard'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $child->id,
            'provider_id' => $doctor->id,
            'status' => 'SCHEDULED',
            'type' => 'PEDIATRIC_CHECKUP',
            'chief_complaint' => 'Mild seasonal cough and sore throat',
        ]);
    }

    public function test_clerk_walkin_registration_and_queue()
    {
        $nurse = User::where('role', 'NURSE')->first();
        $doctor = User::where('role', 'DOCTOR')->first();
        $this->actingAs($nurse);

        // Register walk-in patient without online account (standalone walk-in)
        $response = $this->post(route('clerk.register_walkin'), [
            'first_name' => 'Arthur',
            'last_name' => 'Pendleton',
            'date_of_birth' => '1970-01-15',
            'gender' => 'MALE',
            'phone' => '+15559998877',
            'blood_group' => 'O-',
            'create_online_account' => false,
            'provider_id' => $doctor->id,
            'chief_complaint' => 'Severe seasonal asthma flare',
        ]);

        $response->assertRedirect(route('clerk.queue'));

        $patient = Patient::where('first_name', 'Arthur')->first();
        $this->assertNotNull($patient);
        $this->assertNull($patient->account_id); // Verified standalone walk-in
        $this->assertStringStartsWith('MRN-', $patient->mrn);

        // Verify appointment created in CHECKED_IN status
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'provider_id' => $doctor->id,
            'status' => 'CHECKED_IN',
        ]);
    }

    public function test_doctor_soap_drafting_and_signing_generates_auto_billing()
    {
        $doctor = User::where('role', 'DOCTOR')->first();
        $this->actingAs($doctor);

        $patient = Patient::first();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'provider_id' => $doctor->id,
            'appointment_date' => now(),
            'status' => 'CHECKED_IN',
            'type' => 'CONSULTATION',
            'chief_complaint' => 'Acute migraine with visual aura',
        ]);

        // Start encounter
        $startResponse = $this->post(route('doctor.encounter.start', $appointment->id));
        $encounter = Encounter::where('appointment_id', $appointment->id)->first();
        $startResponse->assertRedirect(route('doctor.encounter.show', $encounter->id));

        // Medicine to prescribe
        $medicine = MedicineCatalog::first();

        // Save draft SOAP notes and eRx
        $draftResponse = $this->post(route('doctor.encounter.draft', $encounter->id), [
            'temperature_c' => 37.1,
            'systolic_bp' => 125,
            'diastolic_bp' => 82,
            'heart_rate_bpm' => 76,
            'weight_kg' => 72.0,
            'height_cm' => 175.0,
            'subjective_notes' => 'Patient complains of throbbing unilateral temporal headache.',
            'objective_notes' => 'Neurological exam benign, pupillary responses normal.',
            'assessment_notes' => 'Migraine with aura, episodic.',
            'plan_notes' => 'Initiate medication, rest in quiet dark environment.',
            'diagnoses' => [
                [
                    'icd10_code' => 'G43.109',
                    'description' => 'Migraine with aura, not intractable',
                    'type' => 'PRIMARY',
                ],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'dosage' => '1 tablet',
                    'frequency' => 'PRN at onset',
                    'duration_days' => 7,
                    'quantity' => 10,
                    'instructions' => 'Take with water at first sign of aura.',
                ],
            ],
        ]);
        $draftResponse->assertSessionHas('success');

        // Sign encounter
        $signResponse = $this->post(route('doctor.encounter.sign', $encounter->id));
        $signResponse->assertRedirect(route('doctor.encounter.show', $encounter->id));

        // Verify encounter is signed
        $encounter->refresh();
        $this->assertTrue($encounter->is_signed);
        $this->assertNotNull($encounter->signed_at);

        // Verify automated invoice generated!
        $invoice = Invoice::where('encounter_id', $encounter->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('UNPAID', $invoice->status);
        $this->assertGreaterThan(0, $invoice->grand_total);

        // Verify invoice has consultation fee item and medicine item
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_type' => 'CONSULTATION',
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_type' => 'MEDICINE',
        ]);
    }

    public function test_audit_logs_recorded_for_phi_operations()
    {
        $doctor = User::where('role', 'DOCTOR')->first();
        $patient = Patient::first();

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
