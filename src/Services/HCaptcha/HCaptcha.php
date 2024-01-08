<?php

namespace Pishehgostar\ExchangeRecaptcha\Services\HCaptcha;

use Pishehgostar\ExchangeRecaptcha\Abstracts\Recaptcha;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

class HCaptcha extends Recaptcha
{
    public function loadScript(): void
    {
        echo "<script src='https://www.hCaptcha.com/1/api.js?hl=".app()->getLocale()."' async defer></script>";
    }

    public function render(string $callback,string $action): void
    {
        $site_key = config('exchange-recaptcha.hcaptcha.site_key');
        echo <<<EOL
            <button class="h-captcha btn btn-primary btn-block btn-md" data-sitekey="$site_key" data-callback="$callback" data-size="invisible">
            Submit
            </button>
EOL;;
    }

    public function verify(string $token,string $action):bool
    {
        $secret_key = config('exchange-recaptcha.hcaptcha.secret_key');
        $data = array(
            'secret' => $secret_key,
            'response' => $token
        );
        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://api.hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);

        $responseData = json_decode($response);
        return (bool)$responseData->success;
    }

    public function getInputName():string
    {
        return config('exchange-recaptcha.hcaptcha.input_name');
    }
}
