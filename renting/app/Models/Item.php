<?php

namespace App\Models;

use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $table = 'rent_items';

    protected $fillable = [
        'qty',
        'rent_id',
        'rent_value',
        'unit_value',
        'equipment_id',
    ];

    protected $casts = [
        'rent_value' => 'float',
        'unit_value' => 'float',
    ];

    protected $appends = ['equipment'];

    public static function booted(): void
    {
        self::saving(function (Item $item) {
            $equipment = $item->equipment;

            if (!empty($equipment)) {
                $increment = 1 + $item->rent->paymentCondition['increment'] / 100;
                $item->unit_value = $equipment['unit_value'] * $item->qty;
                $item->rent_value = $equipment['values'][$item->rent->period_id]['value'] * $increment * $item->qty;
            }
        });
    }

    public function rent(): BelongsTo
    {
        return $this->belongsTo(Rent::class);
    }

    public function equipment(): Attribute
    {
        return Attribute::get(function () {
            $service = app()->make(InventoryService::class);
            return $service->getEquipment($this->equipment_id);
        });
    }
}
