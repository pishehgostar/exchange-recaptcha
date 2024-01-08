<?php
namespace Pishehgostar\ExchangeRecaptcha\Factories;

use Pishehgostar\ExchangeRecaptcha\Contracts\RecaptchaInterface;
use Pishehgostar\ExchangeRecaptcha\Services\AlphaNumeric\AlphaNumeric;
use Pishehgostar\ExchangeRecaptcha\Services\GoogleEnterprise\GoogleEnterprise;
use Pishehgostar\ExchangeRecaptcha\Services\HCaptcha\HCaptcha;

class RecaptchaFactory
{
    /**
     * @throws \Exception
     */
    public static function createInstance(): RecaptchaInterface
    {
        $selectedService = config('exchange-recaptcha.default');
        $services = [
            'google_enterprise' => GoogleEnterprise::class,
            'hcaptcha'=>HCaptcha::class,
            'alpha_numeric'=>AlphaNumeric::class
            // Add more services as needed
        ];

        if (isset($services[$selectedService])) {
            return new $services[$selectedService]();
        }

        throw new \Exception('Please set recaptcha settings correctly.');
    }
}
