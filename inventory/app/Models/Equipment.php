<?php

namespace App\Models;

use App\Services\PricingService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    const UNITS = ['pç', 'mt'];

    protected $fillable = [
        'description',
        'supplier_id',
        'unit',
        'profit_percentage',
        'weight',
        'in_stock',
        'effective_qty',
        'min_qty',
        'purchase_value',
        'unit_value',
        'replace_value',
    ];

    protected $casts = [
        'profit_percentage' => 'float',
        'weight' => 'float',
        'purchase_value' => 'float',
        'unit_value' => 'float',
        'replace_value' => 'float',
    ];

    protected $appends = [
        'values',
    ];

    public function values(): Attribute
    {
        return Attribute::get(function () {
            /** @var PricingService */
            $service = app()->make(PricingService::class);
            return $service->getRentingValues($this->id);
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
