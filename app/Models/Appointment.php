<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_id',
        'provider_id',
        'appointment_date',
        'visit_type',
        'serial_number',
        'token_number',
        'fee_amount',
        'payment_status',
        'paid_at',
        'status',
        'type',
        'chief_complaint',
        'notes',
        'notification_payload',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'paid_at' => 'datetime',
        'fee_amount' => 'decimal:2',
        'notification_payload' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            $date = Carbon::parse($appointment->appointment_date)->toDateString();

            // Calculate daily serial number for the doctor on this date
            if (empty($appointment->serial_number) || $appointment->serial_number === 1) {
                $maxSerial = static::where('provider_id', $appointment->provider_id)
                    ->whereDate('appointment_date', $date)
                    ->max('serial_number');

                $appointment->serial_number = ($maxSerial ?? 0) + 1;
            }

            if (empty($appointment->token_number)) {
                $appointment->token_number = sprintf('TK-%03d', $appointment->serial_number);
            }

            // Determine fee based on visit type if not explicitly passed
            $provider = User::find($appointment->provider_id);
            if ($provider) {
                if ($appointment->visit_type === 'REPORT_VISIT') {
                    $appointment->fee_amount = (float) ($provider->followup_fee ?? 30.00);
                } else {
                    $appointment->fee_amount = (float) ($provider->consultation_fee ?? 75.00);
                }
            }

            // Build automated simulated notification payload
            $patient = Patient::find($appointment->patient_id);
            $doctorName = $provider ? $provider->name : 'Attending Physician';
            $dept = $provider ? ($provider->department ?: $provider->specialization) : 'General Medicine';
            $patientName = $patient ? $patient->full_name : 'Valued Patient';
            $mrn = $patient ? $patient->mrn : 'N/A';
            $timeFormatted = Carbon::parse($appointment->appointment_date)->format('M d, Y h:i A');

            $appointment->notification_payload = [
                'sms' => [
                    'recipient' => $patient ? $patient->phone : 'N/A',
                    'message' => "Synapse EMR: Appointment confirmed for {$patientName} (ID: {$mrn}) with Dr. {$doctorName} ({$dept}) on {$timeFormatted}. Daily Serial: #{$appointment->serial_number} ({$appointment->token_number}). Visit Type: {$appointment->visit_type}.",
                    'dispatched_at' => now()->toISOString(),
                    'status' => 'DELIVERED',
                ],
                'email' => [
                    'recipient' => $patient ? ($patient->email ?: 'household@emr.local') : 'patient@emr.local',
                    'subject' => "Appointment Confirmation & Daily Token #{$appointment->serial_number} - Dr. {$doctorName}",
                    'patient_name' => $patientName,
                    'patient_mrn' => $mrn,
                    'doctor_name' => $doctorName,
                    'department' => $dept,
                    'serial_number' => $appointment->serial_number,
                    'token_number' => $appointment->token_number,
                    'visit_type' => $appointment->visit_type,
                    'fee_amount' => $appointment->fee_amount,
                    'appointment_date' => $timeFormatted,
                    'dispatched_at' => now()->toISOString(),
                    'status' => 'SENT',
                ],
            ];
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

    public function encounter(): HasOne
    {
        return $this->hasOne(Encounter::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'appointment_id');
    }
}
