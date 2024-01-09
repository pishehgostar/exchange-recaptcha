<?php
namespace Pishehgostar\ExchangeRecaptcha\Services\AlphaNumeric;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Captcha
{
    // Configuration Options
    var $word = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var $length = 5;
    var $img_width = 110;
    var $img_height = 40;
    var $font_path = '';
    var $font_size = 30;

    private $uuid;

    public function __construct(array $config = [],?string $key){
        if (is_null($key)){
            $this->uuid = Str::random(5) . now()->getTimestamp() . Str::random(4);
        }else{
            $this->uuid = $key;
        }
        if (count($config) > 0){
            foreach ($config as $key => $val){
                if (isset($this->$key)){
                    $method = 'set_'.$key;
                    if (method_exists($this, $method)){
                        $this->$method($val);
                    }else{
                        $this->$key = $val;
                    }
                }
            }
        }
        if ( ! extension_loaded('gd')){
            return FALSE;
        }
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public static function calculateContrastRatio($textColor)
    {
        $brightness = ($textColor[0] * 299 + $textColor[1] * 587 + $textColor[2] * 114) / 1000;
        return ($brightness >= 128) ? rand(225,255) : rand(0,25); // Return white or black based on brightness
    }

    public static function generateColorWithReadableText()
    {
        $maxAttempts = 1000; // Set a maximum number of attempts to avoid infinite loops

        for ($i = 0; $i < $maxAttempts; $i++) {
            $bgColor = [
                rand(0, 255),
                rand(0, 255),
                rand(0, 255),
            ];

            // Calculate readable text color based on the background color
            $textColor = [
                self::calculateContrastRatio($bgColor),
                self::calculateContrastRatio($bgColor),
                self::calculateContrastRatio($bgColor)
            ];

            // Calculate the contrast ratio manually
            $contrastRatio = ($textColor[0] + 0.05) / ($bgColor[0] + 0.05);

            $acceptableContrastRatio = 4.5; // Adjust as needed

            if ($contrastRatio >= $acceptableContrastRatio) {
                return [$bgColor, $textColor]; // Return colors if they provide sufficient contrast
            }
        }

        return null; // Return null if no suitable colors are found within the attempts limit
    }

    public static function getCaptchaWord($uuid)
    {
        return Cache::get('captcha-an-' .$uuid);
    }

    public function createCaptcha(){
        ob_start();
        $str = '';
        for ($i = 0; $i < $this->length; $i++){
            $str .= substr($this->word, mt_rand(0, strlen($this->word) -1), 1);
        }
        $word = $str;

        $this->saveWord($word);

        /* Determine angle and position */
        $length    = strlen($word);
        $angle    = ($length >= 6) ? rand(-($length-6), ($length-6)) : 0;
        $x_axis    = rand(6, (360/$length)-16);
        $y_axis = ($angle >= 0 ) ? rand($this->img_height, $this->img_width) : rand(6, $this->img_height);

        /* Create image */
        if (function_exists('imagecreatetruecolor')){
            $im = imagecreatetruecolor($this->img_width, $this->img_height);
        }else{
            $im = imagecreate($this->img_width, $this->img_height);
        }


        list($bgColorRgb, $textColorRgb) = self::generateColorWithReadableText();
        $gridColorRgb = [
            ceil($bgColorRgb[0]*0.8),
            ceil($bgColorRgb[1]*0.6),
            ceil($bgColorRgb[2]*0.5),
        ];
        $borderColorRgb = $gridColorRgb;
        $shadowColorRgb = $gridColorRgb;

        $bg_color        = imagecolorallocate ($im, $bgColorRgb[0], $bgColorRgb[1], $bgColorRgb[2]);
        $border_color    = imagecolorallocate ($im, $borderColorRgb[0], $borderColorRgb[1], $borderColorRgb[2]);
        $text_color        = imagecolorallocate ($im, $textColorRgb[0], $textColorRgb[1], $textColorRgb[2]);
        $grid_color        = imagecolorallocate($im, $gridColorRgb[0], $gridColorRgb[1], $gridColorRgb[2]);
        $shadow_color    = imagecolorallocate($im, $shadowColorRgb[0], $shadowColorRgb[1], $shadowColorRgb[2]);

        $randomColor =[
            rand(0,255),
            rand(0,255),
            rand(0,255),
        ];
        $luminance = 0.3 * hexdec($randomColor[0]) + 0.59 * hexdec($randomColor[1]) + 0.11 * hexdec($randomColor[2]);


        /* Create the rectangle */
        ImageFilledRectangle($im, 0, 0, $this->img_width, $this->img_height, $bg_color);

        /* Create the spiral pattern */
        $theta        = 1;
        $thetac        = 7;
        $radius        = 16;
        $circles    = 20;
        $points        = 32;

        for ($i = 0; $i < ($circles * $points) - 1; $i++){
            $theta = $theta + $thetac;
            $rad = $radius * ($i / $points );
            $x = ($rad * cos($theta)) + $x_axis;
            $y = ($rad * sin($theta)) + $y_axis;
            $theta = $theta + $thetac;
            $rad1 = $radius * (($i + 1) / $points);
            $x1 = ($rad1 * cos($theta)) + $x_axis;
            $y1 = ($rad1 * sin($theta )) + $y_axis;
            imageline($im, $x, $y, $x1, $y1, $grid_color);
            $theta = $theta - $thetac;
        }

        /* Write the text in image */
        $use_font = ($this->font_path != '' && file_exists($this->font_path) && function_exists('imagettftext')) ? TRUE : FALSE;

        $x = rand(0, $this->img_width/($length/0.8));
        $y = $this->font_size+2;

        for ($i = 0; $i < strlen($word); $i++)
        {
//            dump("x: $x , y: $y , font-size: {$this->font_size}");
            if ($use_font == FALSE){
                $y = rand(0 , $this->img_height/2);
                imagestring($im, $this->font_size, $x, $y, substr($word, $i, 1), $text_color);
                $x += ($this->font_size * 0.7);
            }else{
                $y = rand($this->img_height/2, $this->img_height-3);
                imagettftext($im, $this->font_size, $angle, $x, $y, $text_color, $this->font_path, substr($word, $i, 1));
                $x += $this->font_size * 0.7;
            }
        }
        /* Create the image border */
        imagerectangle($im, 0, 0, $this->img_width-1, $this->img_height-1, $border_color);

        /* Showing the image */
        imagejpeg($im,NULL,90);

        $content = ob_get_clean();

        imagedestroy($im);//destroying image

        return base64_encode($content);
    }

    public function saveWord($word)
    {
        Cache::put('captcha-an-' . $this->uuid,$word,config('exchange-recaptcha.alpha_numeric.ttl'));
    }

}

