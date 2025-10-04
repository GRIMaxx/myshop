<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Api\ResponseInterface;
use App\Services\Api\ResponseService;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ResponseInterface::class, ResponseService::class);
        $this->app->alias(ResponseInterface::class, 'apiResponse');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
