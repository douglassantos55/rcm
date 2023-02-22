<?php

namespace App\Http\Requests;

use App\Models\Supplier;
use App\Rules\CpfCnpj;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class SupplierRequest extends FormRequest
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
    public function rules(CpfCnpj $cnpj): array
    {
        /** @var Unique */
        $unique = Rule::unique(Supplier::class, 'cnpj');

        if (!is_null($this->route('supplier'))) {
            $unique->ignore($this->route('supplier'));
        }

        return [
            'social_name' => ['required'],
            'legal_name' => ['nullable'],
            'cnpj' => ['nullable', $cnpj, $unique],
            'email' => ['nullable', 'email'],
            'website' => ['nullable', 'url'],
            'phone' => ['nullable', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'state' => ['nullable', 'size:2'],
            'postcode' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],
        ];
    }
}
