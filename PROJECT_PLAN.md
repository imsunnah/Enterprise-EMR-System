# Enterprise EMR System — Execution & Implementation Plan

**Stack:** Laravel 12 (PHP 8.3) · Vue 3 (Composition API) · Inertia.js · Tailwind CSS · PostgreSQL / SQLite

---

## 1. Architectural Blueprint & Domain Modeling

### A. Patient Household & Account Architecture
- **Primary Patient Account (`patient_accounts`):**
  - Identifier: UUID
  - Credentials: `phone_number` (unique), `password_hash`, `otp_code`, `otp_expires_at`, `is_verified`
  - Purpose: Manages authentication for the patient portal.
- **Patient Profiles (`patients`):**
  - Identifier: UUID
  - Household Link: `account_id` (foreign key to `patient_accounts`, nullable for walk-in patients)
  - Medical Record Number: `mrn` (unique, generated format e.g. `MRN-YYYY-XXXXX`)
  - Personal Details: `first_name`, `last_name`, `date_of_birth`, `gender`, `blood_group`, `phone`, `relationship_to_account_holder` (`SELF`, `SPOUSE`, `CHILD`, `PARENT`, `OTHER`)
  - Walk-in Support: When `account_id` is `NULL`, represents walk-in unregistered household patients.

### B. Role-Based Access Control (RBAC) & Security
- **Roles:**
  - `ADMIN`: Full CRUD on users, providers, master catalogs, audit log inspection.
  - `DOCTOR`: Clinical encounters, vitals, SOAP notes, diagnosis tagging (ICD-10), e-prescribing, lab ordering.
  - `NURSE`: Vitals entry, queue triage, encounter viewing (clinical support).
  - `BILLER`: Walk-in patient registration, scheduling, invoice generation, payment processing. **Strictly prohibited from accessing clinical SOAP notes and e-prescriptions (PHI Protection)**.
  - `PATIENT`: Access only to patient profiles linked to their authenticated `patient_account`.
- **Middleware:**
  - `CheckRole`: Route-level enforcement verifying user roles against allowed roles list with PHI data isolation.
  - `HandleInertiaRequests`: Shares authenticated user, active household patient account, and notification flashes.

### C. Immutable Audit Logging
- **Table: `audit_logs`**
  - `id`: UUID
  - `user_id`: UUID of acting user / provider / account
  - `action`: `VIEW`, `CREATE`, `UPDATE`, `DELETE`, `SIGN`, `CHECK_IN`, etc.
  - `entity_name`: `Patient`, `Encounter`, `Prescription`, `Invoice`, etc.
  - `entity_id`: UUID
  - `ip_address`: String
  - `changes`: JSON (snapshot of previous and mutated state)
  - `created_at`: Timestamp
- **Service: `AuditLogger`**
  - Centralized singleton helper invoked whenever sensitive Patient Health Information (PHI) is read or modified.

### D. Master Catalogs & Auto-Pricing Engine
- **Medicine Catalog (`medicine_catalog`):**
  - Attributes: `brand_name`, `generic_name`, `dosage_form`, `strength`, `unit_price`, `discount_percentage`, `stock_quantity`, `is_active`.
- **Lab Test Catalog (`lab_test_catalog`):**
  - Attributes: `test_code`, `test_name`, `category`, `standard_price`, `discount_percentage`, `specimen_type`, `turnaround_hours`, `is_active`.
- **Prescription & Billing Automation:**
  - Prescription line items fetch unit prices and default discounts automatically from `medicine_catalog`.
  - Finished encounters automatically synthesize an itemized invoice via `BillingEngine` itemizing consultation fee, prescribed medicines, and ordered lab tests.

---

## 2. Phased Implementation Roadmap & Verification Status

### Phase 1: Foundation & Project Scaffolding
- [x] PHP 8.3, Composer, and Node.js environments configured.
- [x] Laravel 12 application with Inertia.js Vue 3 adapter.
- [x] Tailwind CSS v4, `@lucide/vue`, and `@vitejs/plugin-vue` configured.

### Phase 2: Database Migrations & Schemas
- [x] Migration 1: `patient_accounts` and `patients` (with MRN generation & household `account_id` relationship).
- [x] Migration 2: `users` (with UUID and RBAC roles: `ADMIN`, `DOCTOR`, `NURSE`, `BILLER`, `PATIENT`).
- [x] Migration 3: `medicine_catalog` and `lab_test_catalog`.
- [x] Migration 4: `appointments` and `encounters` (vitals, SOAP notes, ICD-10 diagnoses).
- [x] Migration 5: `prescriptions`, `prescription_items`, `lab_orders`, `lab_order_items`.
- [x] Migration 6: `invoices`, `invoice_items`, and `audit_logs`.

### Phase 3: Eloquent Models & Domain Services
- [x] Eloquent Models with `HasUuids` trait and relationship definitions:
  - `User`, `PatientAccount`, `Patient`, `Encounter`, `EncounterDiagnosis`, `MedicineCatalog`, `LabTestCatalog`, `Appointment`, `Prescription`, `PrescriptionItem`, `LabOrder`, `LabOrderItem`, `Invoice`, `InvoiceItem`, `AuditLog`.
- [x] Services:
  - `AuditLogger`: Service for immutable PHI logging.
  - `BillingEngine`: Auto-invoice generation from encounter consultation, prescriptions, and lab tests.
- [x] Middleware:
  - `CheckRole`: Strict RBAC validation and PHI isolation.
  - `HandleInertiaRequests`: Shared auth and patient household session context.

### Phase 4: Backend Controllers & API Routes
- [x] `Auth/AuthController`: Staff login, logout, and 1-click test role switcher.
- [x] `Auth/PatientAuthController`: Phone-based OTP verification and household session.
- [x] `Patient/PatientPortalController`: Multi-profile household switcher and family member onboarding.
- [x] `Clerk/QueueController`: Front desk queue, walk-in patient quick-registration, appointment check-in.
- [x] `Doctor/EncounterController`: Split-screen SOAP workspace, vitals entry, catalog search for medications & labs, encounter signing & auto-billing.
- [x] `Admin/CatalogController`: CRUD for Medicine Catalog and Lab Test Catalog, audit log viewer.
- [x] `Billing/BillingController`: Invoicing desk, payment collection, and itemized billing statements.

### Phase 5: Frontend Vue 3 + Inertia Views (Tailwind CSS)
- [x] `AuthenticatedLayout.vue`: Clean sidebar navigation, active role badge, quick role switcher, and alert toasts.
- [x] `Auth/Login.vue`: Staff login with 1-click demo profiles (Doctor, Nurse, Biller, Admin).
- [x] `Auth/PatientLogin.vue`: Phone OTP authentication interface for patient households.
- [x] `PatientPortal/Dashboard.vue`: Household profile switcher cards, tabbed medical history (Clinical Notes, Prescriptions, Lab Results, Invoices, Appointments), and "Add Family Member" modal.
- [x] `Doctor/Index.vue`: Today's patient queue and recent consultations.
- [x] `Doctor/Encounter.vue`: Three-pane clinical layout (Vitals + BMI on left, tabbed SOAP editor + ICD-10 tagger in center, catalog e-prescriptions & lab tests on right, sign action).
- [x] `Clerk/Queue.vue`: Front desk queue with walk-in fast registration modal and appointment scheduler.
- [x] `Admin/Catalogs.vue`: Master setup tables for Medicines, Lab Tests, and immutable Audit Trail.
- [x] `Billing/Index.vue`: Revenue metrics cards, invoice status filters, and payment recording modal.
- [x] `Billing/Show.vue`: Printable itemized billing statement with PHI compliance disclosure.

### Phase 6: Seeders & Verification
- [x] `DatabaseSeeder`: Complete realistic seed data with staff users across all roles, master medicine & lab test catalogs, patient household with 3 members, walk-in patient, clinical encounter, diagnoses, e-prescriptions, lab results, and auto-generated invoice.
- [x] Automated test suite: `EmrSystemTest` with 9 passing tests and 42 assertions validating RBAC, PHI protection, household accounts, and auto-billing.
