<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'department',
        'specialization',
        'license_number',
        'consultation_fee',
        'followup_fee',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'consultation_fee' => 'decimal:2',
            'followup_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'DOCTOR';
    }

    public function isNurse(): bool
    {
        return $this->role === 'NURSE';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'RECEPTIONIST';
    }

    public function isBiller(): bool
    {
        return $this->role === 'BILLER';
    }

    public function isPatient(): bool
    {
        return $this->role === 'PATIENT';
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'provider_id');
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class, 'provider_id');
    }
}
