<?php
namespace Pishehgostar\ExchangeRecaptcha\Contracts;
interface RecaptchaInterface
{
    public function loadScript();

    public function render (string $callback,string $action,string $title,array $attributes);

    public function verify(string $token,string $action):bool;

    public function getInputName():string;
}
