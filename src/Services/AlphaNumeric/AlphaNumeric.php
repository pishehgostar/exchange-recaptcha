<?php

namespace Pishehgostar\ExchangeRecaptcha\Services\AlphaNumeric;

use Illuminate\Support\Facades\Log;
use Pishehgostar\ExchangeRecaptcha\Abstracts\Recaptcha;

class AlphaNumeric extends Recaptcha
{
    public function loadScript()
    {
        $refresh_route = route('ex-auth.refresh-captcha');
        $save_route = route('ex-auth.save-captcha');
        $script = <<<EOL
            <script>
            function refreshCaptcha(id){
                $.post('$refresh_route',{
                    id:id
                },function (data,status){
                    if (status==='success'){
                        var img_selector = '#captcha-img-'+id;
                        var text_selector = '#captcha-text-'+id;
                        var response_selector = '#captcha-response-'+id;
                        $(img_selector).attr('src',data.src)
                        $(text_selector).val('')
                        // $(response_selector).attr('id','captcha-response-' + data.id)
                    }
                })
                // document.getElementById('captcha-' + id).src = "data:image/jpeg;base64,"
            }
$(document).ready(function() {
    $(document).on('click','[data-callback]', function(e) {
        e.preventDefault();
        var captcha_key = $(this).attr('data-key');
        var callbackFunction = $(this).attr('data-callback');
        var value = $("#captcha-text-" + captcha_key).val();
        var response_input = $("#captcha-response-" + captcha_key);
        if (value){
                 $.post('$save_route',{
            id:captcha_key,
            value:value
        },function (data,status){
            if (status==='success' && data){
                response_input.val(data)

        // Check if the callback function exists and is a function
        if (typeof window[callbackFunction] === 'function') {
            // Execute the callback function
            window[callbackFunction]();
        } else {
            // Callback function does not exist or is not a function
            console.error('Callback function not found or is not a function');
        }
            }
        });
        }
    });
});
</script>
EOL;

        echo $script;
    }

    public function getInputName(): string
    {
        return config('exchange-recaptcha.alpha_numeric.input_name');
    }

    public function render(string $callback, string $action,string $title,array $attributes)
    {
        $captcha = new Captcha([
            'font_path' => public_path('fonts/captcha_code.otf'),
        ],null);
        $inputName = $this->getInputName();
        $uuid = $captcha->getUuid();
        $src = $captcha->createCaptcha();
        $attributes = array_merge($attributes,[
            'type'=>'submit',
            'data-callback'=>$callback,
            'data-key'=>$uuid,
        ]);

        $item = view('ex-recaptcha::alpha_numeric.submit',compact('inputName','uuid','src','title','attributes'))->render();

        echo $item;
    }

    public function verify(string $token, string $action): bool
    {
        try {
            $decryptedData = json_decode(base64_decode($token),true);
            $secretKey = config('exchange-recaptcha.alpha_numeric.secret_key');
            if (isset($decryptedData['data']) && isset($decryptedData['iv'])){
                $decrypted = openssl_decrypt(base64_decode($decryptedData['data']), 'aes-256-cbc', $secretKey, 0, base64_decode($decryptedData['iv']));
                $decryptedData = json_decode($decrypted, true);
                if (isset($decryptedData['id']) && isset($decryptedData['value'])){
                    $original_captcha = Captcha::getCaptchaWord($decryptedData['id']);
                    $user_captcha = $decryptedData['value'];
                    if ($original_captcha==$user_captcha){
                        return true;
                    }
                }
            }
        }catch (\Exception $exception){
            Log::info($exception->getMessage());
        }
        return false;

    }
}
