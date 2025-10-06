<?php
/** Обработка маршрутов - поисковой системы **/
namespace App\Services\Search\Helpers;

use App\Contracts\Cache\CacheServiceInterface;
use App\Contracts\Setting\SettingServiceInterface;

class RoutesProvider
{
    private const CACHE_SERVICE = 'meilisearch';    // Имя сервиса по умолчанию
    private const CACHE_ROUTE   = 'routes';         // Тип данных - настройки или конфиг данные
    private const CACHE_TTL     = 86400;            // Время жизни ключа
    private const CACHE_KEY     = 'route_all';      // Ключ для этого провайдера

    /**
     * Времменый кеш в рамках запроса.
     * Request Cache (in-memory, внутри PHP-запроса)
     **/
    private static array $requestCache = [];

    public function __construct(
        private CacheServiceInterface $cache,
        private SettingServiceInterface $settings
    ) {}

    /**
     * Получить все маршруты
     *
     * Ключ кеша: myshop:cache:rt:s_meilisearch:route_all
     *
    **/
    public function getAllRoute() : Array	{

        // Получить драйвер поиска
        $driver = $this->getDriver();

        // Проверяем Request Cache (in-memory, в рамках PHP-запроса)
        if (isset(self::$requestCache[self::CACHE_KEY])) {
            return self::$requestCache[self::CACHE_KEY];
        }

        $data = $this->cache->remember(
            self::CACHE_ROUTE,
            self::CACHE_KEY,
            fn() => [
                'search_input_like'     => route('search.input.like') ?? '/searchl',		// Поиск - Автодополнение (1-3 символа) — только префиксный поиск
                'search_input_fulltext' => route('search.input.fulltext') ?? '/searchf',	// Поиск - Полноценный поиск (4+ символа) — FULLTEXT
            ],
            self::CACHE_TTL,
            $driver
        );

        // Установить для повторного приминения
        self::$requestCache[self::CACHE_KEY] = $data;

        return $data;
    }

    // Получить драцвер поискового ядра
    public function getDriver(): string
    {
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
