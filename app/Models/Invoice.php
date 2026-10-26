<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'encounter_id',
        'appointment_id',
        'invoice_type',
        'subtotal',
        'discount_total',
        'tax_amount',
        'grand_total',
        'amount_paid',
        'balance_due',
        'status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $year = date('Y');
                $randomDigits = str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
                $prefix = $invoice->invoice_type === 'DIAGNOSTIC' ? 'DIAG' : ($invoice->invoice_type === 'CONSULTATION' ? 'CONS' : 'INV');
                $invoice->invoice_number = "{$prefix}-{$year}-{$randomDigits}";
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recalculate(): void
    {
        $subtotal = 0;
        $discountTotal = 0;

        foreach ($this->items as $item) {
            $subtotal += ($item->unit_price * $item->quantity);
            $discountTotal += $item->discount_amount;
        }

        $this->subtotal = round($subtotal, 2);
        $this->discount_total = round($discountTotal, 2);
        $netAfterDiscount = max(0, $this->subtotal - $this->discount_total);
        $this->tax_amount = round($netAfterDiscount * 0.00, 2);
        $this->grand_total = round($netAfterDiscount + $this->tax_amount, 2);
        $this->balance_due = max(0, round($this->grand_total - (float) $this->amount_paid, 2));

        if ((float) $this->amount_paid >= (float) $this->grand_total && (float) $this->grand_total > 0) {
            $this->status = 'PAID';
            if (!$this->paid_at) {
                $this->paid_at = now();
            }
        } elseif ((float) $this->amount_paid > 0) {
            $this->status = 'PARTIALLY_PAID';
        } else {
            $this->status = 'UNPAID';
        }

        $this->save();
    }
}
