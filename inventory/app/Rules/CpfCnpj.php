<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfCnpj implements ValidationRule
{
    private CpfCnpjValidator $validator;

    public function __construct(CpfCnpjValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->validator->setValor($value);
        $attribute = strtolower($attribute);

        $valid_cpf = $attribute === 'cpf' && $this->validator->verifica_sequencia(11);
        $valid_cnpj = $attribute === 'cnpj' && $this->validator->verifica_sequencia(14);

        if ((!$valid_cpf || !$valid_cnpj) && !$this->validator->valida($value)) {
            $fail('The :attribute is invalid.');
        }
    }
}
