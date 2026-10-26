# Comprehensive Electronic Medical Record (EMR) System

An enterprise-grade, multi-tenant Electronic Medical Record (EMR) and Practice Management System built using **Laravel**, **Vue.js 3 (Composition API)**, **Inertia.js**, and **PostgreSQL**. 

Designed for clinics, medical centers, and hospital networks, this system supports **Family Household Accounts** (multiple patient profiles managed under one phone number), **Clerk-Assisted Walk-In Registration**, **Role-Based PHI Access Control**, **Medicine & Lab Test Pricing Catalogs**, **E-Prescribing**, and automated **Revenue Cycle Management (RCM)**.

---

## 🛠 Tech Stack

- **Backend:** Laravel 11+ (PHP 8.3)
- **Frontend:** Vue.js 3 (Composition API, Script Setup)
- **Adapter Layer:** Inertia.js (SPA behavior without decoupled REST complexity)
- **Styling:** Tailwind CSS & Shadcn/Vue components
- **Database:** PostgreSQL (with `uuid-ossp` and `pg_trgm` fuzzy searching)
- **Authentication:** Laravel Fortify / Custom Phone OTP & JWT Session logic
- **Interoperability Standards:** HL7 / FHIR R4 Ready

---

## 📸 Key Features & Architecture Highlights

1. **Family / Household Account Model:**
   - Single phone-number-based login (`patient_accounts`).
   - Multiple family profiles (`patients`) under one account (Self, Spouse, Children, Elderly Parents).
   - Standalone walk-in registration for non-digital patients (`account_id = NULL`).
2. **Strict Role-Based Access Control (RBAC):**
   - 5 isolated user roles: `ADMIN`, `DOCTOR`, `NURSE`, `BILLER`, `PATIENT`.
   - **PHI Protection:** Billing clerks are strictly isolated from viewing clinical SOAP notes and diagnosis details.
3. **Master Setup Catalogs with Discount Engines:**
   - **Medicine Catalog:** Unit price, strength, stock management, and percentage discounts.
   - **Diagnostic Test Catalog:** Standard pricing, LOINC codes, and discount overrides.
4. **Clinical SOAP Encounter Engine:**
   - Structured charting: Subjective, Objective, Assessment, Plan.
   - Integrated Vital Signs trends (BP, Heart Rate, BMI, SpO2, Temp).
   - ICD-10 Diagnosis tagging.
5. **E-Prescribing & Order Entry:**
   - Real-time catalog auto-complete for prescriptions and lab tests.
   - Automatic line-item price calculation with pre-configured discounts feeding directly into the billing desk.
6. **Immutable Audit Logging:**
   - Automated compliance logging tracking all read/write access to Patient Health Information (PHI).

---

## 📦 System Module Breakdown

```
                  ┌────────────────────────────────────────┐
                  │           EMR Core Gateway             │
                  └──────────────────┬─────────────────────┘
                                     │
   ┌───────────────────┬─────────────┴────────────┬───────────────────┐
   ▼                   ▼                          ▼                   ▼
┌──────────────┐ ┌─────────────┐         ┌─────────────────┐ ┌────────────────┐
│ Admin &     │ │ Clinical    │         │ Revenue Cycle   │ │ Patient        │
│ Patient Reg. │ │ Encounter   │         │ (Billing/Claims)│ │ Portal         │
└──────────────┘ └─────────────┘         └─────────────────┘ └────────────────┘
```

### 1. Patient Registration & Master Patient Index (MPI)
- Dynamic household profile management under one phone number.
- Deduplication with fuzzy matching (`pg_trgm`) to prevent duplicate Medical Record Numbers (MRN).
- Emergency contact tracking and insurance details.

### 2. Appointment & Scheduling Engine
- Resource and provider multi-calendar views.
- Walk-in queue handling (*Checked In, In Exam Room, Completed*).
- Online self-booking by family account holders or clerk-assisted phone booking.

### 3. Clinical Charting & SOAP Notes
- Custom clinical templates by specialty.
- Graphing of vital signs over time.
- Standardized ICD-10 diagnosis tagging and active problem lists.

### 4. Master Catalogs (Medicine & Diagnostic Tests)
- **Medicine Setup:** Manage dosage forms, strengths, standard unit rates, and active stock.
- **Lab Test Setup:** LOINC catalog setup with category mapping and standard prices.
- **Discount Rules:** Percentage discounts managed by admins and automatically applied during e-prescribing and invoicing.

### 5. E-Prescribing (eRx) & Lab Ordering
- Medication auto-suggest linked directly to stock and catalog pricing.
- Automated dosage calculators.
- Lab order requests with barcoding/specimen tracking.

### 6. Revenue Cycle Management (RCM) & Invoicing
- Auto-billing generated from finished clinical encounters.
- Support for discounts, co-payments, tax calculation, and partial payments.

---

## 🔒 Role-Based Permission Matrix

| Resource / Module | Admin | Doctor | Nurse | Billing Clerk | Patient |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Patient Demographics** | Full (CRUD) | Read / Update | Read / Update | Read / Update | Read / Update (Family) |
| **SOAP Notes & Diagnosis** | Read (Audit) | Full (Create/Sign)| Vitals / Read | **No Access (PHI)**| Signed Notes Only |
| **eRx & Lab Orders** | Read Only | Full Control | Read / Assist | **No Access** | Read Only |
| **Billing & Invoices** | Full Access | Read Only | **No Access** | Full Control | Read / Pay Online |
| **System Catalogs** | Full Access | Read Only | Read Only | Read Only | **No Access** |

---

## 🗄 Database Schema Design (PostgreSQL)

Here is the underlying database schema supporting multi-patient accounts, catalogs, clinical encounters, and billing:

```sql
-- Enable Extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Enum Definitions
CREATE TYPE user_role AS ENUM ('ADMIN', 'DOCTOR', 'NURSE', 'RECEPTIONIST', 'BILLER', 'PATIENT');
CREATE TYPE gender_type AS ENUM ('MALE', 'FEMALE', 'OTHER');
CREATE TYPE appointment_status AS ENUM ('SCHEDULED', 'CHECKED_IN', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED');
CREATE TYPE invoice_status AS ENUM ('UNPAID', 'PARTIALLY_PAID', 'PAID', 'VOID');

-- 1. Patient Accounts (Phone Authentication)
CREATE TABLE patient_accounts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 2. Patients (Multiple Profiles linked to Account)
CREATE TABLE patients (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    account_id UUID REFERENCES patient_accounts(id) ON DELETE SET NULL,
    mrn VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender gender_type NOT NULL,
    blood_group VARCHAR(5),
    phone VARCHAR(20) NOT NULL,
    relationship_to_account_holder VARCHAR(50) DEFAULT 'SELF',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 3. Catalogs (Medicine & Diagnostic Tests)
CREATE TABLE medicine_catalog (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    brand_name VARCHAR(200) NOT NULL,
    generic_name VARCHAR(200) NOT NULL,
    dosage_form VARCHAR(100) NOT NULL,
    strength VARCHAR(50) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    discount_percentage DECIMAL(5, 2) DEFAULT 0.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE lab_test_catalog (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    test_code VARCHAR(50) UNIQUE NOT NULL,
    test_name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    standard_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    discount_percentage DECIMAL(5, 2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE
);

-- 4. Encounters & SOAP Notes
CREATE TABLE encounters (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    patient_id UUID REFERENCES patients(id),
    provider_id UUID NOT NULL,
    encounter_date TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    temperature_c DECIMAL(4, 2),
    heart_rate_bpm INT,
    systolic_bp INT,
    diastolic_bp INT,
    spo2_percentage DECIMAL(4, 1),
    weight_kg DECIMAL(5, 2),
    subjective_notes TEXT,
    objective_notes TEXT,
    assessment_notes TEXT,
    plan_notes TEXT,
    is_signed BOOLEAN DEFAULT FALSE
);

-- 5. Prescriptions (Catalog Linked)
CREATE TABLE prescription_items (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    encounter_id UUID REFERENCES encounters(id) ON DELETE CASCADE,
    medicine_id UUID REFERENCES medicine_catalog(id),
    dosage VARCHAR(100) NOT NULL,
    frequency VARCHAR(100) NOT NULL,
    duration_days INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    total_price DECIMAL(10, 2) NOT NULL
);

-- 6. Audit Trail
CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_name VARCHAR(50) NOT NULL,
    entity_id UUID NOT NULL,
    ip_address VARCHAR(45),
    changes JSONB,
    timestamp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
```

---

## 📂 Project Folder Structure (Laravel + Vue + Inertia)

```text
emr-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                # Catalog (Medicines & Tests) & User Management
│   │   │   ├── Doctor/               # SOAP Charting & eRx Controllers
│   │   │   ├── Clerk/                # Walk-In & Appointment Controllers
│   │   │   ├── Patient/              # Patient Portal & Profile Switcher
│   │   │   └── Billing/              # Invoice & Payment Processing
│   │   ├── Middleware/
│   │   │   └── CheckRole.php         # RBAC Enforcer Middleware
│   │   └── Requests/                 # Form Validation Requests
│   ├── Models/
│   │   ├── PatientAccount.php        # Phone Account Model
│   │   ├── Patient.php               # Family Profile Model
│   │   ├── MedicineCatalog.php       # Medicine Setup Model
│   │   ├── LabTestCatalog.php        # Lab Setup Model
│   │   └── Encounter.php             # SOAP Notes Model
│   └── Services/
│       └── AuditLogger.php           # Immutable PHI Audit Service
│
└── resources/
    └── js/
        ├── Layouts/                  # AuthenticatedLayout, AppLayout
        ├── Pages/
        │   ├── Admin/                # Catalog Management Views
        │   ├── Doctor/               # SOAP Charting Workspace
        │   ├── Clerk/                # Front Desk Queue & Walk-In Modal
        │   ├── PatientPortal/        # Family Switcher & Medical Records
        │   └── Billing/              # Invoices & Checkout Desk
        └── app.js                    # Inertia App Entry Point
```

---

## 🚀 Quick Start Guide

### Prerequisites
- **PHP** >= 8.3
- **Composer**
- **Node.js** >= 18.x & NPM
- **PostgreSQL** >= 14

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-org/emr-laravel-vue.git
   cd emr-laravel-vue
   ```

2. **Install PHP and Node dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment Variables:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Configure your PostgreSQL connection in `.env`:*
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=emr_db
   DB_USERNAME=postgres
   DB_PASSWORD=your_password
   ```

4. **Run Migrations & Database Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Start Development Servers:**
   ```bash
   # Terminal 1 - Backend Server
   php artisan serve

   # Terminal 2 - Vite Frontend Builder
   npm run dev
   ```

6. **Access Application:**
   Open your browser and navigate to `http://localhost:8000`.

---

## 📋 Implementation Roadmap

- [x] **Phase 1:** Core Authentication, Phone OTP, Multi-Patient Profile linking.
- [x] **Phase 2:** Master Catalog Management (Medicines & Tests) with discount calculations.
- [x] **Phase 3:** Clinical SOAP Charting, Vitals recording, and e-prescribing.
- [ ] **Phase 4:** WebRTC Telehealth integration & FHIR R4 API endpoints.
- [ ] **Phase 5:** AI Ambient Scribe (Voice-to-SOAP transcription).

---

## 📜 License
This software is released under the [MIT License](LICENSE).