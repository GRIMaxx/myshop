<?php

namespace App\Services\Search\Helpers;

use App\Contracts\Cache\CacheServiceInterface;
use App\Contracts\Setting\SettingServiceInterface;

class TranslationsProvider
{
    private const CACHE_SERVICE = 'meilisearch';    // Имя сервиса по умолчанию
    private const CACHE_TRANS   = 'translations';   // Тип данных - настройки или конфиг данные
    private const CACHE_TTL     = 86400;            // Время жизни ключа
    private const CACHE_KEY     = 'trans_all';      // Ключ для этого провайдера

    /**
     * Времменый кеш в рамках запроса.
     * Request Cache (in-memory, внутри PHP-запроса)
     **/
    private static array $requestCache = [];

    public function __construct(
        private CacheServiceInterface $cache,
        private SettingServiceInterface $settings
    ) {}

    // Список переводов
    public function Trans(): array {
        return [
            'all' => [// Переводы используються не один раз и в разных фичах
                'image' 					=> __('Image'),
                'digital_product' 			=> __('Digital product'),
                'close' 					=> __('Close'),
                'selected' 					=> __('Selected'),
                'find' 						=> __('Find...'),
                'not_found' 				=> __('Not found.'),
                'showMore' 					=> __('Показать ещё'),
                'found' 					=> __('Found'),
            ],
            'results' => [// Используються в блоке вывода результатов поиска
                'nothing_found_1'   		=> __("Nothing found matching your request"),

                // promo
                'sale' 						=> __("Sale"),
                'discounts' 				=> __("Discounts"),
                'limited_product' 			=> __("Limited product"),
                'gift_with_purchase'		=> __("Gift with purchase"),
                'one_plus_one_equals_three' => __("When buying: 1 + 1 = 3"),
                'recommended_by_experts' 	=> __("Recommended by experts"),
                'additional_warranty'		=> __("Additional Warranty"),
                'eco_natural' 				=> __("Eco/Natural"),
                'bought_100_people_today' 	=> __("Bought 100+ people today"),

                // availability_delivery
                'in_stock' 					=> __("In stock"),
                'pre_order' 				=> __("Pre-order"),
                'fast_delivery' 			=> __("Fast delivery"),
                'dispatch_today_in_24_hours'=> __("Dispatch today / in 24 hours"),
                'pick_up_from_warehouse' 	=> __("Pick up from warehouse"),

                // rating_reviews
                'top_rated' 				=> __("Top rated (4+ stars)"),
                'bestsellers' 				=> __("Bestsellers"),
                'most_discussed' 			=> __("Most discussed"),

                'search_mob' 				=> __("Search the products"),
                'search_lg' 				=> __("Search the products"),
                'maxChars' 					=> __("Maximum character limit exceeded (max 50)"),
                'invalidRequest' 			=> __("Invalid request"),
                'serverError' 				=> __("Something went wrong! (Server error)"),
                'requestCancelled' 			=> __("Request cancelled!"),
                'foundInProducts' 			=> __("Найдено в товарах"),
                'foundInStores' 			=> __("Найдено в магазинах"),
                'foundInVerticalMenu' 		=> __("Найдено в верикальном меню"),
                'foundInHorizontalMenu' 	=> __("Найдено в горизонтальном меню"),

                'TopProducts' 				=> __('Топовые товары'),
                'TopCategories' 			=> __('Топовые категории'),
                'TopBrands' 				=> __('Топовые бренды'),
                'TopStores' 				=> __('Топовые магазины'),
                'TopSellers' 				=> __('Топовые продавцы'),
            ],
            'filters' => [
                // search_in
                'search_in' 				=> __("Search in:"),

                // promotions
                'promotions' 				=> __("Promotions"),

                // countries
                'countries' 				=> __('Countries'),
                'popular_countries' 		=> __('Popular Countries'),
                'other_countries' 			=> __('Other Countries'),
                'search_countries' 			=> __('Search countries...'),
                'available_countries' 		=> __('Available countries'),
                'no_countries_found' 		=> __('No countries found matching your search'),
                'no_countries_available' 	=> __('No countries available'),

                // price
                'price' 					=> __("Price"),

                // brands
                'brands' 					=> __("Brands"),
                'popular_brands' 			=> __("Popular Brands"),
                'other_brands' 				=> __("Other Brands"),
                'search_brands' 			=> __("Search brands..."),
                'available_brands' 			=> __("Available brands"),
                'no_brands_found' 			=> __("No brands found matching your search"),
                'no_brands_available' 		=> __("No brands available"),

                // categories
                'categories' 				=> __("Categories"),
                'popular_categories'		=> __("Popular Categories"),
                'other_categories' 			=> __("Other Categories"),
                'available_categories' 		=> __("Available categories"),
                'no_categories_found' 		=> __("No categories found matching your search"),
                'no_categories_available' 	=> __("No categories available"),
                'search_categories' 		=> __("Search categories..."),

                // Availability / Delivery
                'availability_delivery' 	=> __("Availability / Delivery"),

                // all
                'only_popular' 				=> __("Only popular"),
                'of' 						=> __("of "),
                'filter_disabled' 			=> __("Filter disabled"),
                'reset' 					=> __("Сбросить"),
                'filters' 					=> __("Filters"),

                // rating_reviews
                'rating_reviews' 			=> __("Rating / Reviews"),
            ]
        ];
    }

    // Получить переводы
    // Ключ кеша: myshop:cache:i18n:s_meilisearch:trans_all
    public function getTranslations() : Array	{

        // Получить драйвер поиска
        $driver = $this->getDriver();

        // Проверяем Request Cache (in-memory, в рамках PHP-запроса)
        if (isset(self::$requestCache[self::CACHE_KEY])) {
            return self::$requestCache[self::CACHE_KEY];
        }

        $data = $this->cache->remember(
            self::CACHE_TRANS,
            self::CACHE_KEY,
            fn() => $this->Trans(),
            self::CACHE_TTL,
            $driver
        );

        // Установить для повторного приминения
        self::$requestCache[self::CACHE_KEY] = $data;

        return $data;
    }

    // Получить драцвер поискового ядра
    public function getDriver(): string {
        // Проверяем кэш на уровне текущего запроса
        if (isset(self::$requestCache['name_service'])) {
            return self::$requestCache['name_service'];
        }

        $driver = data_get($this->settings->get('search'), 'driver', null);

        // Если в настройках нет — fallback на config(), а если и там нет — жёсткий дефолт
        if (empty($driver)) {
            $driver = config('settings.search.driver', self::CACHE_SERVICE);
        }

        // Нормализуем (например, на случай если кто-то руками вписал "ElasticSearch" вместо "elasticsearch")
        $driver = strtolower(trim($driver));

        // Кладём в requestCache
        self::$requestCache['name_service'] = $driver;

        return $driver;
    }
}
