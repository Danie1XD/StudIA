<?php

namespace App\Providers;

use App\Services\AI\GeminiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton: el contenedor crea UN solo cliente ya configurado y lo reutiliza donde se inyecte
        $this->app->singleton(GeminiClient::class, fn () => new GeminiClient(
            apiKey: (string) config('services.gemini.key'),
            modelo: (string) config('services.gemini.model'),
            nivelPensamiento: (string) config('services.gemini.thinking_level'),
            timeout: (int) config('services.gemini.timeout'),
        ));
    }

    public function boot(): void
    {
        //
    }
}