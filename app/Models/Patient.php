<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'account_id',
        'mrn',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'phone',
        'email',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'relationship_to_account_holder',
        'insurance_provider',
        'insurance_policy_number',
        'allergies',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected $appends = ['full_name', 'age', 'is_walk_in'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->mrn)) {
                $year = date('Y');
                $randomDigits = str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
                $patient->mrn = "MRN-{$year}-{$randomDigits}";
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class, 'account_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->orderBy('appointment_date', 'desc');
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class)->orderBy('encounter_date', 'desc');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderBy('created_at', 'desc');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class)->orderBy('created_at', 'desc');
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class)->orderBy('created_at', 'desc');
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    public function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null,
        );
    }

    public function isWalkIn(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->account_id === null,
        );
    }
}
