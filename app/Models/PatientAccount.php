<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class PatientAccount extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'phone_number',
        'password_hash',
        'otp_code',
        'otp_expires_at',
        'is_verified',
    ];

    protected $hidden = [
        'password_hash',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'account_id')->orderBy('relationship_to_account_holder');
    }

    public function primaryPatient(): ?Patient
    {
        return $this->patients()->where('relationship_to_account_holder', 'SELF')->first()
            ?: $this->patients()->first();
    }

    public function verifyPassword(string $password): bool
    {
        if (!$this->password_hash) {
            return false;
        }
        return Hash::check($password, $this->password_hash);
    }
}
