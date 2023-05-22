<?php

namespace App\Models;

use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rent extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'start_date',
        'end_date',
        'qty_days',
        'discount',
        'paid_value',
        'delivery_value',
        'bill',
        'check_info',
        'delivery_address',
        'usage_address',
        'discount_reason',
        'observations',
        'transporter',
        'customer_id',
        'period_id',
        'payment_type_id',
        'payment_method_id',
        'payment_condition_id',
    ];

    protected $with = ['items'];

    protected static function booted(): void
    {
        static::retrieved(function (Rent $rent) {
            /** @var InventoryService */
            $service = app()->make(InventoryService::class);
            $items = $rent->items->pluck('equipment_id')->all();

            if (!empty($items)) {
                $service->getEquipment($items);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
