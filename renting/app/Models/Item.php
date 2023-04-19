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

    protected $appends = ['equipment'];

    public static function booted(): void
    {
        self::saving(function (Item $item) {
            if (!empty($item->equipment)) {
                $item->rent_value = $item->equipment['rent_value'] * $item->qty;
                $item->unit_value = $item->equipment['unit_value'] * $item->qty;
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
