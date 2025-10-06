<?php
/**
    Обработка - конфиг даных поисковой системы

 **/
namespace App\Services\Search\Helpers;

use App\Contracts\Cache\CacheServiceInterface;
use App\Contracts\Setting\SettingServiceInterface;

//
class ConfigProvider
{
    private const CACHE_SERVICE = 'meilisearch';    // Имя сервиса по умолчанию
    private const CACHE_CONFIG  = 'config';         // Тип данных - настройки или конфиг данные
    private const CACHE_TTL     = 86400;            // Время жизни ключа
    private const CACHE_KEY     = 'config_all';     // Ключ для этого провайдера

    /**
     * Времменый кеш в рамках запроса.
     * Request Cache (in-memory, внутри PHP-запроса)
     **/
    private static array $requestCache = [];

    public function __construct(
        private CacheServiceInterface $cache,
        private SettingServiceInterface $settings
    ) {}

    // После разработки:
    // 1 - Доработать получать даные из группы 1 + если нет добавить дубликаты сюда - config\settings.php как ЖБ вариант
    // 2 - Разкоментировать кеш - я нарошно закоментировал

    // Ключ для : myshop:cache:cfg:s_meilisearch:config_all -- ключ в чистом виде!

    // При обновлении или добавлении новых даных обезательно удалить старый кеш метод уже подготовлен.
    // $cache->forget(
    //       type: self::CACHE_CONFIG,
    //       key: 'config_all',
    //       service: self::CACHE_SERVICE
    // );

    public function getAllConfiguration() : Array	{

        // Получить драйвер поиска
        $driver = $this->getDriver();

        // Проверяем Request Cache (in-memory, в рамках PHP-запроса)
        if (isset(self::$requestCache[self::CACHE_KEY])) {
            return self::$requestCache[self::CACHE_KEY];
        }

        $data = $this->cache->remember(
            self::CACHE_CONFIG,
            self::CACHE_KEY,
            fn() => [
                'show_console_ error'   => true,        	// Выводить логирования в консоль браузера bool
                'debounce' 			    => 500,        		// Чтобы уменьшить количество запросов на сервер, используем lodash.debounce (При установке рекомендую минимум 500)
                'maxQueryLength' 	    => 50,				// Максимальное колво символо (после полной очистки от запрещенных символов) должно быть для отправки запроса на поиск.
                'blacklist_simvols'     => [            	// Запрещённые символы - все они будут удалены из строки поиска на этапе отправки запроса - это значит пользователь не увидет изменения
                    '<', '>', '{', '}', '[', ']', '(', ')', '=', ';',
                    '"', "'", '`', '\\', '/', '|', '&', '^', '%', '$',
                    '#', '@', '*', '!'
                ],
                //'show_group_title'      => false,            // Показать заголовок группы при успешно найденом товаре
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
