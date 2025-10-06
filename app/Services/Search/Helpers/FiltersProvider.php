<?php
/**
 * -- Обработка фильтров

 **/
namespace App\Services\Search\Helpers;

use App\Contracts\Cache\CacheServiceInterface;
use App\Contracts\Setting\SettingServiceInterface;
use App\Models\Filter;
use Astrotomic\Translatable\Locales;
use Illuminate\Support\Arr;

class FiltersProvider
{
    private const CACHE_SERVICE = 'meilisearch';    // Имя сервиса по умолчанию
    private const CACHE_FILTER  = 'filter';         // Тип данных - настройки или конфиг данные
    private const CACHE_TTL     = 86400;            // Время жизни ключа
    private const CACHE_KEY     = 'filter_all';     // Ключ для этого провайдера

    /**
     * Времменый кеш в рамках запроса.
     * Request Cache (in-memory, внутри PHP-запроса)
     **/
    private static array $requestCache = [];

    public function __construct(
        private CacheServiceInterface $cache,
        private SettingServiceInterface $settings,
        private Locales $locales
    ) {}

    //
    public function getFormattedFilters(bool $onlyVisible = true): array {
        $filters = $this->getFilters($onlyVisible);
        $result = [];
        foreach ($filters as $filter) {
            $filterArray = $this->formatFilter($filter);
            $result[$filter['key']] = $filterArray;
        }
        return $result;
    }

    protected function formatFilter(array $filter): array {
        return [
            'type' 					=> $filter['type'],
            'visible' 				=> (bool)$filter['visible'],
            'searchable' 			=> (bool)$filter['searchable'],
            'activation' 			=> $filter['activation'],
            'show_popular_first' 	=> (bool)$filter['show_popular_first'],
            'show_popular_group' 	=> (bool)$filter['show_popular_group'],
            'show_grouped_group' 	=> (bool)$filter['show_grouped_group'],
            'show_ungrouped_group' 	=> (bool)$filter['show_ungrouped_group'],
            'show_icon' 			=> (bool)$filter['show_icon'],
            'grouping' 				=> $filter['grouping'] ? json_decode($filter['grouping'], true) : [],
            'options' 				=> $this->formatOptions($filter['options'], $filter['type'])
        ];
    }

    protected function formatOptions(array $options, string $filterType): array
    {
        $formatted = [];

        foreach ($options as $option) {
            $optionKey = $option['option_key'];

            $formatted[$optionKey] = [
                'default' 			=> (bool)$option['default'],
                'count'             => (int)($option['count'] ?? 0),
                'popular' 			=> (bool)$option['popular'],
                'icon' 				=> $option['icon'],
                'color' 			=> $option['color'] ?? '',

                /**
                 * Поиск при помощи -> Meilisearch для мультиязычного поиска
                 * (например, клиент может искать и на ru, и на en, и на uk) → тогда можно разкоментироать все переводы.
                 * но это только память и лишние данные.
                 **/
                //'translations' 		=> $this->formatTranslations($option['translations']),

                /**
                 * Если поиск и фронт работает только на текущей локали → тащить все переводы нет смысла,
                 * это только память и лишние данные. Лучше отдавать только App::getLocale()
                 * или ту локаль, что нужна.
                 *
                 * Когда создам сервис для мульти язычности тут возможно придеться поменять !!!
                 **/
                'translation' => $this->getTranslationByLocale($option['translations'], app()->getLocale()),

                'metadata'          => $option['metadata'] ?? []
            ];

            if ($filterType === 'range_slider') {
                $formatted[$optionKey] = array_merge($formatted[$optionKey], [
                    'startMin' 		=> $option['start_min'],
                    'startMax' 		=> $option['start_max'],
                    'min' 			=> $option['min'],
                    'max' 			=> $option['max'],
                    'step' 			=> $option['step'],
                    'tooltipPrefix' => $option['tooltip_prefix'],
                    'tooltipSuffix' => $option['tooltip_suffix'],
                    'unit' 			=> $option['unit']
                ]);
            }
        }

        return $formatted;
    }

    protected function getTranslationByLocale(array $translations, string $locale): string
    {
        foreach ($translations as $translation) {
            if ($translation['locale'] === $locale) {
                return $translation['name'];
            }
        }

        // fallback — если перевода на локали нет
        return $translations[0]['name'] ?? '';
    }

    protected function formatTranslations(array $translations): array
    {
        $result = [];

        foreach ($translations as $translation) {
            $result[$translation['locale']] = $translation['name'];
        }

        return $result;
    }

    /**
     * Получает фильтры
     */
    public function getFilters(bool $onlyVisible = true): array {

        $query = Filter::with(['options' => function($query) {
            $query->orderBy('sort_order', 'asc')        // сортировка опций
            ->orderBy('popular', 'desc')                // опции по популярности
            ->orderBy('count', 'desc')                  // если популярность равна, fallback
            ->orderBy('id', 'asc');                     // окончательный fallback
        }])
            ->orderBy('sort_order', 'asc')          // сортировка фильтров
            ->orderBy('key', 'asc');                // fallback по ключу

        if ($onlyVisible) {
            $query->where('visible', true);
        }

        return $query->get()->toArray();
    }

    // Получить даные фильтров по умолчанию:
    // Ключ кеша: myshop:cache:flt:s_meilisearch:filter_all_visible
    public function getAllFilters(bool $onlyVisible = true) : array {

        $cacheKey = self::CACHE_KEY . ($onlyVisible ? '_visible' : '_all');

        // Получить драйвер поиска
        $driver = $this->getDriver();

        // Проверяем Request Cache (in-memory, в рамках PHP-запроса)
        if (isset(self::$requestCache[$cacheKey])) {
            return self::$requestCache[$cacheKey];
        }

        $data = $this->cache->remember(
            self::CACHE_FILTER,
            $cacheKey,
            fn() => $this->getFormattedFilters($onlyVisible),
            self::CACHE_TTL,
            $driver
        );

        // Установить для повторного приминения
        self::$requestCache[$cacheKey] = $data;

        return $data;
    }

    // Получить драйвер поискового ядра
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

    // ----------------------------------------------------------------------------------------------------------------

    /**
     * Создает или обновляет фильтр с опциями
     *
     * Как использовать:
     *
     * $filterService->createOrUpdateFilter([
     *      'key'                => 'countries',
     *      'type'                => 'checkbox_list',
     *      'visible'            => true,
     *      'searchable'        => true,
     *      'activation'        => 'manual',
     *      'show_popular_first'=> true,
     *      'show_icon'        => true,
     *      'options' => [
     *
     *          'ukraine' => [
     *              'default'    => false,
     *              'popular'    => true,
     *              'icon'        => 'flag-ukraine',
     *              'translations' => [
     *              'en' => 'Ukraine',
     *              'ru' => 'Украина',
     *              'uk' => 'Україна',
     *              ]
     *          ],
     *
     *          'germany' => [
     *              'default' => true,
     *              'translations' => [
     *                  'en' => 'Germany',
     *                  'ru' => 'Германия',
     *              ]
     *          ]
     *      ]
     * ]);
     * */
    public function createOrUpdateFilter(array $data): Filter {
        // Создаем/обновляем фильтр
        $filter = $this->updateFilterData($data);

        // Обрабатываем опции если они есть
        if (isset($data['options']) && is_array($data['options'])) {
            $this->processOptions($filter, $data['options']);
        }

        // Очищаем кеш конкретного фильтра
        //$this->cacheService->clearFiltersCache();
        // app(CacheServiceInterface::class)->forget('data','filters','search');
        // Установка

        return $filter->load('options.translations');
    }

    //
    protected function updateFilterData(array $data): Filter {

        $filterData = Arr::only($data, [
            'key',
            'type',
            'visible',
            'searchable',
            'activation',
            'show_popular_first',
            'show_popular_group',
            'show_grouped_group',
            'show_ungrouped_group',
            'show_icon',
            'grouping'
        ]);

        // Явное преобразование grouping в JSON
        if (isset($filterData['grouping'])) {
            $filterData['grouping'] = json_encode($filterData['grouping']);
        }

        return Filter::updateOrCreate(
            ['key' => $data['key']],
            $filterData
        );
    }

    protected function processOptions(Filter $filter, array $options): void {
        foreach ($options as $optionKey => $optionData) {
            // Основные данные опции
            $optionModelData = Arr::only($optionData, [
                'default',
                'count',
                'popular',
                'icon',
                'color',
                'start_min',
                'start_max',
                'min',
                'max',
                'step',
                'tooltip_prefix',
                'tooltip_suffix',
                'unit',
                'metadata',
                'group'
            ]);

            // Создаем или обновляем опцию
            $option = FilterOption::updateOrCreate(
                [
                    'filter_key' => $filter->key,
                    'option_key' => $optionKey
                ],
                $optionModelData
            );

            // Обрабатываем переводы
            $this->processOptionTranslations($option, $optionData);
        }
    }

    /**
     * Обрабатывает переводы для опции
     *
     * @param FilterOption $option
     * @param array $optionData
     */
    protected function processOptionTranslations(FilterOption $option, array $optionData): void {
        // Если переводы переданы в подмассиве 'translations'
        if (isset($optionData['translations']) && is_array($optionData['translations'])) {
            foreach ($optionData['translations'] as $locale => $translation) {
                $this->updateOptionTranslation($option, $locale, [
                    'name'        => $translation['name'] ?? $translation,
                    'description' => $translation['description'] ?? null
                ]);
            }
        }
        // Если переводы переданы как прямыми полями (для обратной совместимости)
        elseif (isset($optionData['name'])) {
            foreach ($this->locales->all() as $locale) {
                if (isset($optionData["name_$locale"])) {
                    $this->updateOptionTranslation($option, $locale, [
                        'name'        => $optionData["name_$locale"],
                        'description' => $optionData["description_$locale"] ?? null
                    ]);
                }
            }
        }
    }

    /**
     * Обновляет конкретный перевод для опции
     *
     * @param FilterOption $option
     * @param string $locale
     * @param array $translationData
     */
    protected function updateOptionTranslation(FilterOption $option, string $locale, array $translationData): void {
        $option->translateOrNew($locale)->fill($translationData)->save();
    }
}
