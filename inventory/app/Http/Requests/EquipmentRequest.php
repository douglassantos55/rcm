<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
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
        ];
    }
}
