<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
