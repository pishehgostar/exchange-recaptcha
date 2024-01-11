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

    public function render(string $callback,string $action,string $title,array $attributes): void
    {
        $site_key = config('ex-recaptcha.hcaptcha.site_key');
        $attributes = array_merge($attributes,[
            'type'=>'submit',
            'data-callback'=>$callback,
            'data-sitekey'=>$site_key,
            'data-size'=>'invisible'
        ]);
        $attributes['class'] = 'h-captcha ' . ($attributes['class']??'');

        $item = view('ex-recaptcha::hcaptcha.submit',compact('title','attributes'))->render();

        echo $item;
    }

    public function verify(string $token,string $action):bool
    {
        $secret_key = config('ex-recaptcha.hcaptcha.secret_key');
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
        return config('ex-recaptcha.hcaptcha.input_name');
    }
}
