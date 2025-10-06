<?php
/**
 * -- Поисковая система на ядре Meilisearch
 *
 *
 * .\components\search-lg.blade.php - Шаблон
 **/
namespace App\Services\Search;

use App\Contracts\Search\SearchServiceInterface;
use App\Contracts\Api\ResponseInterface;

// -- Вспомогательные провайдеры с методами

use App\Services\Search\Helpers\ConfigProvider;
use App\Services\Search\Helpers\FiltersProvider;
use App\Services\Search\Helpers\RoutesProvider;
use App\Services\Search\Helpers\TopDataProvider;
use App\Services\Search\Helpers\TranslationsProvider;
use Illuminate\Support\Facades\Log;

class MeilisearchSearchService implements SearchServiceInterface
{
    /**
     * Времменый кеш в рамках запроса.
     * Request Cache (in-memory, внутри PHP-запроса)
     **/
    private static array $requestCache = [];

    public function __construct(
        private ResponseInterface $response,
        private ConfigProvider $configProvider,
        private FiltersProvider $filtersProvider,
        private RoutesProvider $routesProvider,
        private TopDataProvider $topDataProvider,
        private TranslationsProvider $translationsProvider
    ) {}

    /**
     * Получить все даные для поисковой системы.
     * Метод запускаеться здесь: .\resources\views\layouts\app.blade в переменную --> $globalSearchConfigJson
     * Да не лучшее место но зато запуск 1 раз + проверка
     *
     * -- Данный метод запрашуеться здесь:
     *    .\resources\views\components\search-lg.blade.php
     *    .\resources\views\components\search-md.blade.php
     *
     * -- Ключи кешей:
     *    myshop:cache:cfg:s_meilisearch:config_all             - config
     *    myshop:cache:rt:s_meilisearch:route_all               - routes
     *    myshop:cache:i18n:s_meilisearch:trans_all             - trans
     *    myshop:cache:flt:s_meilisearch:filter_all_visible     - filters
     *
     * Тут есь один нуанс чтобы не собирать даные вообще нудно отключить 2 поля из 2 пока так.
    **/
    public function getAllConfigArray($showLg = false, $showMd = false) : array  {
        try {

            $data = [];

            if ($showLg || $showMd) {

                // Получить из временного кеша в рамках запроса
                // Вернет весь массив и все полученные ранее данные если ключ имеет их
                if (isset(self::$requestCache['data:meilisearch:configAll'])) {
                    return self::$requestCache['data:meilisearch:configAll'];
                }

                // Получить все данные
                // Кеширование в каждом из методов на местах для гипкости.
                // Например если где-то запрос не на все даные а только на определенные getAllConfiguration()
                // Или если будет новая установка только конфиг даных нужно только вметоде getAllConfiguration удалить кеш и все...

                //
                $data = [
                    'config'    => $this->configProvider->getAllConfiguration(),
                    'routes'    => $this->routesProvider->getAllRoute(),
                    'filters'  => $this->filtersProvider->getAllFilters(true),
                    //'tops'     => $this->topProvider->getTopItems(),
                    //'buttons'  => $this->buttonsProvider->getDopButtons(),
                    'trans'    => $this->translationsProvider->getTranslations(),
                ];

                // Только временный кеш для одного запроса
                self::$requestCache['data:meilisearch:configAll'] = $data;
            }

            return $data;

       } catch (\Throwable $e) {
            // Логируем на русском
            Log::error("Ошибка при получении конфигурации поиска", [
                'сообщение' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            // Возвращаем пустой массив, чтобы Blade не падал
            return [];
        }
    }
}

/**
 * namespace App\Services\Search;
 *
 * use Meilisearch\Client;
 * use App\Contracts\Search\SearchServiceInterface;
 *
 * class MeilisearchSearchService implements SearchServiceInterface
 * {
 * protected Client $client;
 * protected $index;
 *
 * public function __construct()
 * {
 * $this->client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
 * $this->index = $this->client->index('products');
 * }
 *
 * public function search(string $query, array $filters = [], int $limit = 20): array
 * {
 * return $this->index->search($query, [
 * 'filter' => $filters,
 * 'limit'  => $limit,
 * ])->getHits();
 * }
 *
 * public function index(array $data): bool
 * {
 * $this->index->addDocuments($data);
 * return true;
 * }
 *
 * public function delete(int|string $id): bool
 * {
 * $this->index->deleteDocument($id);
 * return true;
 * }
 * }
 **/
