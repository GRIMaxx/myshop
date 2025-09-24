<?php
/**
 * Централизованное управления всем кешем в одном месте
 *
 * -- Схема формирования ключей пример: ------------------------------------------------------------------
 *
 * тип:сервис:ключ
 *
 * type:    'data',           - Тип данных (data, config, translations)
 * key:     'website_name',   - Уникальный ключ
 * service: 'settings'        - Сервис-владелец
 *
 * Результат в Redis:  data:settings:website_name
 *
 * Первая часть (data)        — категория данных (что это?)
 * Вторая часть (settings)    — кто эти данные использует (сервис)
 * Третья часть (website_name) — конкретный ключ
 *
 * Примеры правильных ключей
 *
 * Название сайта (настройки)     data:settings:website_name        $cache->put('data', 'website_name', $value, service: 'settings')
 * Поисковый индекс (поиск)       search:search:product_index       $cache->put('search', 'product_index', $value, service: 'search')
 * Переводы интерфейса (поиск)    translations:search:ui_buttons    $cache->put('translations', 'ui_buttons', $value, service: 'search')
 *
 * -- Подключение CacheService в разных частях приложения: -----------------------------------------------
 *
 * В сервисах (SearchService, FilterService, Middleware)
 *
 * use App\Contracts\Cache\CacheServiceInterface;
 * public function __construct(
 * private CacheServiceInterface $cache
 * ) {}
 * $this->cache->remember(....
 * Если сервис подключен в \app\Providers\AppServiceProvider.php
 * не забываем передать :
 * $this->app->singleton(*::class, function ($app) {
 * return new *(
 * $app->make(CacheServiceInterface::class),
 * ...
 * );
 * });
 * В моделях (через трейт)
 * use App\Contracts\Cache\CacheServiceInterface;
 * trait Cacheable
 * {
 * protected function cache(): CacheServiceInterface
 * {
 * return app(CacheServiceInterface::class);
 * }
 * }
 * class Product extends Model
 * {
 * use Cacheable;  <--
 *
 * public function getCachedAttributes(): array
 * {
 * return $this->cache()->remember( <---
 */
namespace App\Services\Cache;

use App\Contracts\Cache\CacheServiceInterface;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class RedisCacheService implements CacheServiceInterface
{
    // Список префиксов для разных типов данных:
    // Для строгости чтобы знать что где будет.
    // type: <---
    public const TYPEPREFIXES = [
        //Тип (type)		Префикс	   Описание

        // -- Системные данные
        'routes'		=> 'rt',	// Маршруты приложения
        'env'			=> 'env',	// Переменные окружения
        'translations'	=> 'i18n',  // Локализованные тексты
        'view'          => 'vw',    // Кеширование представлений
        'config'	    => 'cfg', 	// Системные настройки из config/ файлов (только для Laravel-конфигов из config/)
        'settings'	    => 'set',	// Пользовательские/админские настройки (хранятся в БД - для настроек, которые меняют пользователи/админы)
        'data'	        => 'data',	// Произвольные служебные данные сервисов (для параметров работы модулей (payment, delivery и т.д.))	data:search:index_version
        'options'	    => 'opt', 	// Настройки функциональных модулей	(для технических данных сервисов)        opt:payment:methods

        // -- База данных & Модели
        'model'			=> 'mdl',	// Данные моделей (Eloquent)
        'query'			=> 'qry',	// Результаты сложных SQL-запросов
        'schema'		=> 'sch',	// Структура БД (миграции)

        // -- Пользовательские данные
        'user'			=> 'usr',	// Данные пользователей
        'session'		=> 'ssn',	// Сессии
        'cart'			=> 'cart',	// Корзины покупок

        // -- Контент
        'page'			=> 'pg',	// HTML-страницы
        'block'			=> 'blk',	// Блоки контента (CMS)
        'media'			=> 'mda',	// Медиафайлы (пути, метаданные)

        // -- Сервисы
        'search'		=> 'srch',	// Поисковые индексы
        'filter'		=> 'flt',	// Фильтры каталога
        'api'			=> 'api',	// Ответы внешних API
        'payment'		=> 'pay',	// Платежные операции

        // -- Временные данные
        'temp'			=> 'tmp',	// Временные данные (TTL: минуты)
        'lock'			=> 'lock',	// Блокировки процессов
        'queue'			=> 'que',	// Данные очередей
    ];

    // Префиксы для разных названий сервисов или групп
    // Имена сервисов,моделей,контроллеров,и так далее
    // s_  - Имя сервиса добавляем суффикс
    // m_  - Для моделей добавляем суффикс
    // k_  - Контроллеров добавляем суффикс
    // service: <--
    public const PREFIXES = [
        //Тип (service)	Префикс	       Описание

        'settings' 	=> 's_cfg',		// Сервис настройки всего сайта
        'search' 	=> 's_srch',    // Сервис поисковой системы сайта
        'filters' 	=> 's_flt',     // Сервис фильтры для поисковой системы сайта
        'upload'    => 's_upl',     // Сервис обработки файлов
    ];

    /**
     * Получить данные из кеша по ключу
     *
     * Пример:
     * $value = $cache->get(
     *      'translations',          - Тип данных (translations, filters и т.д.) Каккой тип даных будет храниться например переводы или данные...
     *      'en',                    - Ключ записи
     *      'search'                 - [Опционально] Сервис-источник (PREFIXES)
     * );
     */
    public function get(string $type, string $key, ?string $service = null): mixed
    {
        try {
            $fullKey = $this->buildKey($type, $key, $service);
            $value = Redis::get($fullKey);

            if ($value === null) {
                return null;
            }

            $prefix = substr($value, 0, 2);
            $payload = substr($value, 2);

            return match ($prefix) {
                'j:' => json_decode($payload, true),
                's:' => unserialize($payload),
                default => $payload,
            };
        } catch (\Throwable $e) {
            Log::error("Redis get error: {$e->getMessage()}", compact('type', 'key', 'service'));
            return null;
        }
    }

    /**
     * Получить из кеша или сохранить результат callback
     *
     * Пример:
     * $data = $cache->remember('search', 'product_index', fn() => $service->buildIndex(), 1800, 'catalog');
     * $data = $cache->remember(
     *      'search',                                   // TYPEPREFIXES
     *      'product_index',
     *      fn() => $searchService->buildIndex(),       // Колбек, если данных нет в кеше
     *      1800,                                       // Время жизни в секундах (по умолчанию 3600)
     *      'catalog'                                   // PREFIXES
     *  );
     */
    public function remember(string $type, string $key, Closure $callback, int|Carbon|null $ttl = null, ?string $service = null): mixed
    {
        $value = $this->get($type, $key, $service);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($type, $key, $value, $ttl, $service);

        return $value;
    }

    /**
     * Сохранить данные в кеш
     *
     * Пример:
     * $success = $cache->put(
     *         type:    'filters',
     *         key:     'categories',
     *         value:   $categoriesData,        // Данные для сохранения
     *         ttl:     86400,                // Время жизни если null то год
     *         service: 'menu'
     * );
     */
    public function put(string $type, string $key, mixed $value, int|Carbon|null $ttl = null, ?string $service = null): bool
    {
        try {
            $ttlSeconds = $ttl instanceof Carbon ? $ttl->diffInSeconds(now()) : ($ttl ?? 31536000);
            $fullKey = $this->buildKey($type, $key, $service);

            if (is_array($value) || is_scalar($value)) {
                $stored = 'j:' . json_encode($value);
            } else {
                $stored = 's:' . serialize($value);
            }

            return (bool) Redis::setex($fullKey, $ttlSeconds, $stored);
        } catch (\Throwable $e) {
            Log::error("Redis put error: {$e->getMessage()}", compact('type', 'key', 'service'));
            return false;
        }
    }

    /**
     * Удалить запись из кеша
     *
     * Пример:
     * $cache->forget(
     *         type: 'translations',
     *         key: 'ru',
     *         service: 'settings' // [Опционально] Для точечного удаления
     *  );
     */
    public function forget(string $type, string $key, ?string $service = null): bool
    {
        try {
            $fullKey = $this->buildKey($type, $key, $service);
            return (bool) Redis::del($fullKey);
        } catch (\Throwable $e) {
            Log::error("Redis forget error: {$e->getMessage()}", compact('type', 'key', 'service'));
            return false;
        }
    }

    /**
     * Инвалидация с точностью до сервиса
     * Массово очищает кеш по паттерну работает как в тегированом варианте flush
     * Пример:
     * -- Очистить все переводы поиска:
     * $cache->invalidate(
     *         type: 'translations',
     *         service: 'search'
     * );
     *
     * -- Очистить конкретный ключ фильтров:
     * $cache->invalidate(
     *         type: 'filters',
     *         key: 'price_ranges'
     * );
     */
    public function invalidate(string $type, ?string $service = null, ?string $key = null): void
    {
        try {
            $pattern = $this->buildKey($type, $key ?? '*', $service);
            $keys = Redis::keys($pattern);

            if (!empty($keys)) {
                Redis::del(...$keys);
            }
        } catch (\Throwable $e) {
            Log::error("Redis invalidate error: {$e->getMessage()}", compact('type', 'key', 'service'));
        }
    }

    /**
     * Специализированный метод для получения переводов сервиса
     *
     * Пример:
     * $translations = $cache->getTranslations(
     *         service: 'search',   // Имя сервиса (search, settings и т.д.)
     *         locale: 'uk',        // Локаль
     *         callback: fn() => $searchService->loadTranslations('uk') // Колбек для генерации
     *  );
     */
    public function getTranslations(string $service, string $locale, Closure $callback): array
    {
        return $this->remember('translations', $locale, $callback, null, $service) ?? [];
    }

    /**
     * Очистить все переводы сервиса
     *
     * Пример:
     * -- После обновления переводов поиска:
     * $cache->invalidateServiceTranslations('search');
     * -- Для настроек:
     * $cache->invalidateServiceTranslations('settings');
     */
    public function invalidateServiceTranslations(string $service): void
    {
        $this->invalidate('translations', $service);
    }

    /**
     * Построить ключ для Redis (валидация типов и сервисов)
     * Генерирует ключ для Redis в формате type:service:key
     * Строго типизированное создание ключей кеша
     * Пример:
     * $key = $cache->buildKey(
     *         type:    'filters',
     *         key:     'countries',
     *         service: 'geo'
     *  );
     * Возвращает пример: "filters:geo:countries"   (filters(type):geo(service):countries(key))
     * Вернёт: "flt:s_geo:countries"
     */
    public function buildKey(string $type, string $key, ?string $service = null): string
    {
        if (!array_key_exists($type, self::TYPEPREFIXES)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid cache type "%s". Allowed types: %s',
                $type,
                implode(', ', array_keys(self::TYPEPREFIXES))
            ));
        }

        if ($service !== null && !array_key_exists($service, self::PREFIXES)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid service "%s". Allowed services: %s',
                $service,
                implode(', ', array_keys(self::PREFIXES))
            ));
        }

        $typePrefix = self::TYPEPREFIXES[$type];
        $servicePrefix = $service ? self::PREFIXES[$service] : 'global';

        return "{$typePrefix}:{$servicePrefix}:{$key}";
    }
}
