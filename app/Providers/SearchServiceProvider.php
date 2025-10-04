<?php

namespace App\Providers;

use App\Contracts\Search\SearchServiceInterface;

use App\Services\Search\MeilisearchSearchService;
use App\Services\Search\DatabaseSearchService;
use App\Services\Search\ElasticsearchSearchService;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class SearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SearchServiceInterface::class, function ($app) {

            // Получаем медод поиска.
            $settings = app('settings');
            $driver = $settings->get('search.driver', config('settings.search.driver', 'elasticsearch'));

            return match ($driver) {
                'meilisearch' => $app->make(MeilisearchSearchService::class),
                'database'    => $app->make(DatabaseSearchService::class),
                default       => $app->make(ElasticsearchSearchService::class),
            };
        });
        $this->app->alias(SearchServiceInterface::class, 'search');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Получить и Обьявить глобально а определеный шаблон переменные для поисковой системы.
        $this->registerSearchViewComposers();
    }

    protected function registerSearchViewComposers(): void
    {
        View::composer("components.search-lg", function ($view) {
            try {
                $settings = app('settings');
                $search_show_lg = $settings->get('search_show_lg', config('settings.search_show_lg')) ?? false;
                $search_show_md = $settings->get('search_show_md', config('settings.search_show_md')) ?? false;

                // Получить и передать все настройки поисковой системы
                $config = app('search')->getAllConfigArray();

                $view->with([
                    'globalSearchConfigJson'=> $config,
                    'searchShowLg'          => $search_show_lg, // Показать поле поиска для устройств с шириной экрана 992 и более
                    'searchShowMd'          => $search_show_md, // Показать поле поиска для устройств с шириной экрана менее 992
                ]);

            } catch (\Exception $e) {
                // Ошибку не показуем а тихо логируем
                Log::error('Search view composer error', ['error' => $e]);

                // отправить даные во избежания ошибок
                $view->with([
                    'globalSearchConfigJson'=> [],
                    'searchShowLg'     		=> false,
                    'searchShowMd'     		=> false
                ]);
            }
        });
    }
}
