<?php

namespace Pishehgostar\ExchangeRecaptcha\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Pishehgostar\ExchangeRecaptcha\Contracts\RecaptchaInterface;

class Recaptcha implements ValidationRule
{
    public function __construct(private readonly RecaptchaInterface $recaptcha,public string $action)
    {

    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->recaptcha->verify($value,$this->action)){
            $fail('ex-recaptcha::errors.recaptcha')->translate();
        }
    }

}
