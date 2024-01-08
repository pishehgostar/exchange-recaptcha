<?php

namespace Pishehgostar\ExchangeRecaptcha;

use Illuminate\Support\ServiceProvider;
use Pishehgostar\ExchangeRecaptcha\Contracts\RecaptchaInterface;
use Pishehgostar\ExchangeRecaptcha\Factories\RecaptchaFactory;

class MainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/exchange-recaptcha.php', 'exchange-recaptcha'
        );
        $this->app->bind(RecaptchaInterface::class, function ($app) {
            return RecaptchaFactory::createInstance();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/routes.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'ex-recaptcha');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang','ex-recaptcha');
        $this->publishes([
            __DIR__ . '/config/exchange-recaptcha.php' => config_path('exchange-recaptcha.php')
        ]);
    }
}
