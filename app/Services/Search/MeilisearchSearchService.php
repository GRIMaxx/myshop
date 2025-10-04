<?php
/**
 *
 *
 *
 *
 * .\components\search-lg.blade.php - Шаблон
 **/
namespace App\Services\Search;

use App\Contracts\Search\SearchServiceInterface;
use App\Contracts\Cache\CacheServiceInterface;
use App\Contracts\Api\ResponseInterface;

// -- Вспомогательные провайдеры с методами

use App\Services\Search\Helpers\ConfigProvider;
use App\Services\Search\Helpers\FiltersProvider;
use App\Services\Search\Helpers\RoutesProvider;
use App\Services\Search\Helpers\TopDataProvider;
use App\Services\Search\Helpers\TranslationsProvider;

//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
//use App\Contracts\Cache\CacheServiceInterface;
//use function App\Services\Setting\handleNotFound;
class MeilisearchSearchService implements SearchServiceInterface
{
    public function __construct(

        private CacheServiceInterface $cache,
        private ResponseInterface $response,
        private ConfigProvider $configProvider,
        private FiltersProvider $filtersProvider,
        private RoutesProvider $routesProvider,
        private TopDataProvider $topDataProvider,
        private TranslationsProvider $translationsProvider
    ) {}

    public function getAllConfigArray() : array  {

        //dd($this->cache);


        return [1,2,3,4,5,6];
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
