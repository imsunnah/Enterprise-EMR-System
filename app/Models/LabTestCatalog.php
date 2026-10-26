<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTestCatalog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lab_test_catalog';

    protected $fillable = [
        'test_code',
        'test_name',
        'category',
        'standard_price',
        'discount_percentage',
        'specimen_type',
        'turnaround_hours',
        'is_active',
    ];

    protected $casts = [
        'standard_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'turnaround_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(LabOrderItem::class, 'lab_test_id');
    }

    public function calculateDiscountedPrice(): float
    {
        $discountFactor = 1 - ((float) $this->discount_percentage / 100);
        return round((float) $this->standard_price * $discountFactor, 2);
    }
}
