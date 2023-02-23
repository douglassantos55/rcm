<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => ['required'],
            'unit' => ['required', Rule::in(Equipment::UNITS)],
            'supplier_id' => ['nullable', 'exists:App\Models\Supplier,id'],
            'profit_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'weight' => ['nullable', 'numeric'],
            'in_stock' => ['required', 'integer'],
            'effective_qty' => ['required', 'integer'],
            'min_qty' => ['nullable', 'integer'],
            'purchase_value' => ['required', 'numeric'],
            'unit_value' => ['required', 'numeric'],
            'replace_value' => ['required', 'numeric'],
        ]);

        return Equipment::create($validated);
    }
}
