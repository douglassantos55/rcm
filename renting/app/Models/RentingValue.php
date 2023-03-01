<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentingValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'equipment_id',
        'period_id',
    ];
}
