<?php
/**
    Централизованное управления всем кешем в одном месте,
    здесь будут все теги их будет легче контролировать не создавая дубли и ошибки...
    При тестах здесь можно за раз очистить любой кеш и так далее...
 */
namespace App\Providers;

use App\Services\Cache\RedisCacheService;       // Используем прямые запросы к Redis
use App\Contracts\Cache\CacheServiceInterface;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрация CacheService через интерфейс CacheServiceInterface
        $this->app->singleton(CacheServiceInterface::class, function () {
            return new RedisCacheService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Здесь можно добавить логику для автозагрузки кеш-сервисов, если потребуется
    }
}
