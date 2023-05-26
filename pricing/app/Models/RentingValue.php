<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentingValue extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'value',
        'equipment_id',
        'period_id',
    ];

    protected $casts = [
        'value' => 'float',
    ];
}
