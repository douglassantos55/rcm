<?php

namespace App\Models;

use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\PricingService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected $appends = [
        'total',
        'period',
        'remaining',
        'payment_type',
        'payment_method',
        'payment_condition',
    ];

    protected $casts = [
        'bill' => 'float',
        'discount' => 'float',
        'delivery_value' => 'float',
        'paid_value' => 'float',
    ];

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

    public function total(): Attribute
    {
        return Attribute::get(function () {
            return $this->items->reduce(function ($total, $item) {
                return $total + $item->rent_value;
            }, 0) + $this->delivery_value - $this->discount;
        });
    }

    public function remaining(): Attribute
    {
        return Attribute::get(function () {
            return $this->total - $this->paid_value;
        });
    }

    public function period(): Attribute
    {
        return Attribute::get(function () {
            $service = app()->make(PricingService::class);
            return $service->getPeriod($this->period_id);
        });
    }

    public function paymentType(): Attribute
    {
        return Attribute::get(function () {
            $service = app()->make(PaymentService::class);
            return $service->getPaymentType($this->payment_type_id);
        });
    }

    public function paymentMethod(): Attribute
    {
        return Attribute::get(function () {
            $service = app()->make(PaymentService::class);
            return $service->getPaymentMethod($this->payment_method_id);
        });
    }

    public function paymentCondition(): Attribute
    {
        return Attribute::get(function () {
            $service = app()->make(PaymentService::class);
            return $service->getPaymentCondition($this->payment_condition_id);
        });
    }
}
