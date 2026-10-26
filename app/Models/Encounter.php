<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Encounter extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_id',
        'provider_id',
        'appointment_id',
        'encounter_date',
        'temperature_c',
        'heart_rate_bpm',
        'systolic_bp',
        'diastolic_bp',
        'spo2_percentage',
        'weight_kg',
        'height_cm',
        'bmi',
        'subjective_notes',
        'objective_notes',
        'assessment_notes',
        'plan_notes',
        'is_signed',
        'signed_at',
    ];

    protected $casts = [
        'encounter_date' => 'datetime',
        'signed_at' => 'datetime',
        'is_signed' => 'boolean',
        'temperature_c' => 'decimal:2',
        'heart_rate_bpm' => 'integer',
        'systolic_bp' => 'integer',
        'diastolic_bp' => 'integer',
        'spo2_percentage' => 'decimal:1',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'bmi' => 'decimal:1',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($encounter) {
            if ($encounter->weight_kg && $encounter->height_cm && $encounter->height_cm > 0) {
                $heightM = $encounter->height_cm / 100;
                $encounter->bmi = round($encounter->weight_kg / ($heightM * $heightM), 1);
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(EncounterDiagnosis::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function prescriptionItems(): HasManyThrough
    {
        return $this->hasManyThrough(PrescriptionItem::class, Prescription::class);
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
