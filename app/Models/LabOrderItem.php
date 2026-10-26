<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabOrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'lab_order_id',
        'lab_test_id',
        'standard_price',
        'discount_percentage',
        'discount_amount',
        'net_price',
        'billing_status',
        'invoice_id',
        'result_value',
        'reference_range',
        'result_status',
        'result_notes',
    ];

    protected $casts = [
        'standard_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTestCatalog::class, 'lab_test_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
