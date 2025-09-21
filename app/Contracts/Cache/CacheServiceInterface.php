<?php

namespace App\Contracts\Cache;

use Closure;
use Carbon\Carbon;

interface CacheServiceInterface
{
    /**
     * Получить данные из кеша
     *
     * Пример:
     * $value = $cache->get('translations', 'en', 'search');
     */
    public function get(string $type, string $key, ?string $service = null): mixed;

    /**
     * Получить данные из кеша или сохранить результат callback
     *
     * Пример:
     * $data = $cache->remember('search', 'product_index', fn() => $service->buildIndex(), 1800, 'catalog');
     */
    public function remember(string $type, string $key, Closure $callback, int|Carbon|null $ttl = 3600, ?string $service = null): mixed;

    /**
     * Сохранить данные в кеш
     *
     * Пример:
     * $cache->put('filters', 'categories', $categoriesData, 86400, 'menu');
     */
    public function put(string $type, string $key, mixed $value, int|Carbon|null $ttl = 3600, ?string $service = null): bool;

    /**
     * Удалить запись из кеша
     *
     * Пример:
     * $cache->forget('translations', 'ru', 'settings');
     */
    public function forget(string $type, string $key, ?string $service = null): bool;

    /**
     * Массовая очистка кеша по паттерну
     *
     * Пример:
     * $cache->invalidate('translations', 'search');
     */
    public function invalidate(string $type, ?string $service = null, ?string $key = null): void;

    /**
     * Получить переводы для сервиса
     *
     * Пример:
     * $translations = $cache->getTranslations('search', 'uk', fn() => $service->loadTranslations('uk'));
     */
    public function getTranslations(string $service, string $locale, Closure $callback): array;

    /**
     * Очистить все переводы сервиса
     *
     * Пример:
     * $cache->invalidateServiceTranslations('search');
     */
    public function invalidateServiceTranslations(string $service): void;

    /**
     * Построить ключ для Redis (валидация типов и сервисов)
     *
     * Пример:
     * $key = $cache->buildKey('filters', 'countries', 'geo');
     * // Вернёт: "flt:s_geo:countries"
     */
    public function buildKey(string $type, string $key, ?string $service = null): string;
}
