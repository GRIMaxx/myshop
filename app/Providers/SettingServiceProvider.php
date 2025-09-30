<?php
/**
    Обработка настроек сайта
    Получить данные пример: app('settings')->get()
 **/
namespace App\Providers;

use App\Services\Setting\SettingService;
use App\Contracts\Setting\SettingServiceInterface;
use App\Contracts\Cache\CacheServiceInterface;
use Illuminate\Support\ServiceProvider;
class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрируем сервис настроек через интерфейс
        $this->app->singleton(SettingServiceInterface::class, function ($app) {
            return new SettingService(
                $app->make(CacheServiceInterface::class) // внедряем кэш
            );
        });

        // Алиас 'settings' для быстрого доступа через app('settings')
        $this->app->alias(SettingServiceInterface::class, 'settings');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
