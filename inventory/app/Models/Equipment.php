<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'effective_stock',
        'min_qty',
        'purchase_value',
        'unit_value',
        'replace_value',
    ];
}
