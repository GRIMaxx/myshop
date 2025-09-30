<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Получаем настройки для технических параметров приложения.
        // Получения данных в обход SettingService сервиса так как он еще не запущен
        // Но он также возмет данные здесь
        $settings = include storage_path('settings.php');

        $lang = app()->getLocale(); // В будущем подменить на мульти язычность!
        $trans = include lang_path($lang . '/settings/general.php');

        // -- Получить данные если нет взять по умолчанию

        $theme = $settings['theme_page'] ?? config('settings.theme_page');
        $pwa   = $settings['pwa'] ?? config('settings.pwa');
        $store = $settings['store'] ?? config('settings.default');
        $site_name = $trans['site_name_'. $lang] ?? config('settings.site_name');

        // -- Глобальный шаринг для всех view .blade.php

        View::share('theme_page', $theme);
        View::share('pwa', $pwa);
        View::share('store', $store);
        View::share('site_name', $site_name);










    }
}
