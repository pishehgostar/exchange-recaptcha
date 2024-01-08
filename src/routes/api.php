<?php

use Illuminate\Support\Facades\Route;
use Pishehgostar\ExchangeRecaptcha\Services\AlphaNumeric\Captcha;

Route::post('refresh-captcha',function (){
    if (request()->filled('id')){
        $id = request('id');
        $captcha = new Captcha([
            'font_path' => public_path('fonts/captcha_code.otf'),
        ],$id);
        return [
            'src'=>"data:image/jpeg;base64,{$captcha->createCaptcha()}",
            'id'=>$captcha->getUuid()
        ];
    }
})->name('ex-auth.refresh-captcha');

Route::post('captcha',function (){
    if (request()->filled('id') && request()->filled('value')){
        $captcha_key = (string) request()->input('id');
        $value = (string) request()->input('value');
        $word = Captcha::getCaptchaWord($captcha_key);
        if ($value == $word){
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $secretKey = config('exchange-recaptcha.alpha_numeric.secret_key');

            $data = [
                'id'=>$captcha_key,
                'value'=>$value
            ];

            $jsonData = json_encode($data);
            $encrypted = openssl_encrypt($jsonData, 'aes-256-cbc', $secretKey, 0, $iv);

            $encryptedData = [
                "data" => base64_encode($encrypted),
                "iv" => base64_encode($iv)
            ];

            return base64_encode(json_encode($encryptedData));
        }
    }
})->name('ex-auth.save-captcha');
