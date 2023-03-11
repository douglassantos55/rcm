<?php

namespace App\Rules;

use App\Http\Services\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Exists implements ValidationRule
{
    /**
     * @var Service
     */
    private $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->service->has($attribute, $value)) {
            $fail('The selected :attribute is invalid.');
        }
    }
}
