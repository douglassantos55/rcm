<?php

namespace App\Http\Requests;

use App\Http\Services\InventoryService;
use App\Http\Services\PaymentService;
use App\Http\Services\PricingService;
use App\Models\Customer;
use App\Rules\Exists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RentRequest extends FormRequest
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
    public function rules(
        PaymentService $paymentService,
        InventoryService $inventoryService,
        PricingService $pricingService,
    ): array {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'qty_days' => ['required', 'integer'],
            'discount' => ['nullable', 'numeric'],
            'paid_value' => ['nullable', 'numeric'],
            'delivery_value' => ['nullable', 'numeric'],
            'bill' => ['nullable', 'numeric'],
            'payment_type_id' => ['required', new Exists($paymentService)],
            'payment_method_id' => ['required', new Exists($paymentService)],
            'payment_condition_id' => ['required', new Exists($paymentService)],
            'customer_id' => [
                'required',
                Rule::exists(Customer::class, 'id')->withoutTrashed(),
            ],
            'period_id' => ['required', new Exists($pricingService)],
            'items' => ['required'],
            'items.*.qty' => ['required', 'integer'],
            'items.*.equipment_id' => ['required', new Exists($inventoryService)],
        ];
    }
}
