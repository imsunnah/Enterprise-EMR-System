<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineCatalog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'medicine_catalog';

    protected $fillable = [
        'brand_name',
        'generic_name',
        'dosage_form',
        'strength',
        'unit_price',
        'discount_percentage',
        'stock_quantity',
        'is_active',
        'description',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'medicine_id');
    }

    public function calculateDiscountedPrice(): float
    {
        $discountFactor = 1 - ((float) $this->discount_percentage / 100);
        return round((float) $this->unit_price * $discountFactor, 2);
    }

    public function isInStock(int $requiredQuantity = 1): bool
    {
        return $this->stock_quantity >= $requiredQuantity;
    }
}
