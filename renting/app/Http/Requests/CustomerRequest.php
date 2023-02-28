<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Rules\CpfCnpj;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
    public function rules(CpfCnpj $cpfCnpj): array
    {
        $uniqueEmail = Rule::unique(Customer::class, 'email');
        $uniqueCpfCnpj = Rule::unique(Customer::class, 'cpf_cnpj');

        if ($this->route('customer')) {
            $uniqueEmail->ignore($this->route('customer'));
            $uniqueCpfCnpj->ignore($this->route('customer'));
        }

        return [
            'name' => ['required'],
            'email' => ['nullable', 'email', $uniqueEmail],
            'cpf_cnpj' => ['nullable', $cpfCnpj, $uniqueCpfCnpj],
            'state' => ['nullable', 'size:2'],
            'postcode' => ['nullable', 'regex:/^\d{5}-\d{3}$/'],
        ];
    }
}
