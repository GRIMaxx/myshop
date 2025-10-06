<?php
/**
	GX_17.08.2025
	Контроллер для обработки, хранения информации о поисковых запросах и так далее..
    Двух этапный поиск данных
**/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Services\SearchService;

class SearchController extends Controller
{
	public function __construct(SearchService $search) {
		$this->search = $search;
	}

	/**
	* Валидация поискового запроса
	*
	* @param Request $request
	* @return \Illuminate\Validation\Validator
	*/
    protected function validateSearchRequest(Request $request, $max = 50, $min = 4) {

		// Предопределенные списки допустимых значений для фильтров
        $allowedFilters = [
            'promo' => [
                'additional_warranty',
                'limited_product',
                'bought_100_people_today',
                'gift_with_purchase',
                'sale',
                'recommended_by_experts',
                'discounts',
                'eco_natural',
                'one_plus_one_equals_three',
            ],
            'availability_delivery' => [
                'vfast_deliery',
                'in_stock',
                'dispatch_today_in_24_hours',
                'pre_order',
                'pick_up_from_warehouse',
            ],
            'rating_reviews' => [
                'most_discussed',
                'bestsellers',
                'top_rated',
            ],
            'search_in' => ['product', 'digital_product', 'seller', 'store'],
            // Для стран, брендов и категорий будем проверять только формат,
            // так как список может часто меняться и быть большим
        ];

		$rules = [
            'q' => [
				'required', 'string', "min:{$min}", "max:{$max}",
                // Исключаем опасные символы
				// \p{L} 	— соответствует любым буквам из всех языков (включая кириллицу, арабский, китайский и т.д.).
				// \p{N} 	— соответствует любым числам.
				// \s 		— соответствует пробельным символам (например, пробел, табуляция).
				// \- 		— допускает дефисы.
				// ^...$ 	— указывает на то, что вся строка должна соответствовать шаблону (от начала до конца).
				// u 		— модификатор Unicode, чтобы регулярное выражение корректно работало с символами всех языков.
				// \.,     — точки запятые
				// \'"     — апострофы
				'regex:/^[\p{L}\p{N}\s\-\.,\'"]+$/u',
                'not_regex:/<[^>]*>/', // Запрещаем HTML-теги
            ],
            'filters' => 'required|array',
		];

        $customMessages = [
            'q.required' 		=> 'Поле для поиска обязательно.',
            'q.min' 			=> 'Поисковый запрос должен содержать не менее 1 символа.',
            'q.max' 			=> 'Поисковый запрос не может превышать 50 символов.',
            'q.regex' 			=> 'Поисковый запрос содержит недопустимые символы.',
            'q.not_regex' 		=> 'Поисковый запрос содержит HTML-теги.',
            'filters.required' 	=> 'Фильтры обязательны для заполнения.',
            'filters.array' 	=> 'Фильтры должны быть в формате массива.',
        ];

        // Добавляем правила для фильтров
        $rules['filters.price']   = 'required|array|size:2';
        $rules['filters.price.0'] = 'required|numeric|min:0|max:500000000';
        $rules['filters.price.1'] = 'required|numeric|min:0|max:500000000|gte:filters.price.0';
        $customMessages['filters.price.required'] = 'Фильтр цены обязателен.';
        $customMessages['filters.price.size'] = 'Фильтр цены должен содержать 2 значения.';
        $customMessages['filters.price.0.min'] = 'Минимальная цена не может быть меньше 0.';
        $customMessages['filters.price.1.max'] = 'Максимальная цена не может превышать 500,000,000.';
        $customMessages['filters.price.1.gte'] = 'Максимальная цена должна быть больше или равна минимальной.';

        // Правила для promo (если есть)
        $rules['filters.promo'] = 'sometimes|array';
        $rules['filters.promo.*'] = 'string|in:' . implode(',', $allowedFilters['promo']);
        $customMessages['filters.promo.*.in'] = 'Один из выбранных промо-фильтров недопустим.';

        // Правила для availability_delivery (если есть)
        $rules['filters.availability_delivery'] = 'sometimes|array';
        $rules['filters.availability_delivery.*'] = 'string|in:' . implode(',', $allowedFilters['availability_delivery']);
        $customMessages['filters.availability_delivery.*.in'] = 'Один из выбранных фильтров доставки недопустим.';

        // Правила для rating_reviews (если есть)
        $rules['filters.rating_reviews'] = 'sometimes|array';
        $rules['filters.rating_reviews.*'] = 'string|in:' . implode(',', $allowedFilters['rating_reviews']);
        $customMessages['filters.rating_reviews.*.in'] = 'Один из выбранных фильтров рейтинга недопустим.';

        // Правила для search_in (обязательное)
        $rules['filters.search_in'] = 'required|string|in:' . implode(',', $allowedFilters['search_in']);
        $customMessages['filters.search_in.in'] = 'Недопустимое значение для поиска.';

        // Правила для countries (если есть)
        $rules['filters.countries'] = 'sometimes|array';
        $rules['filters.countries.*'] = 'string|max:50|regex:/^[\p{L}\s\-]+$/u';
        $customMessages['filters.countries.*.regex'] = 'Название страны содержит недопустимые символы.';

        // Правила для brands (если есть)
        $rules['filters.brands'] = 'sometimes|array';
        $rules['filters.brands.*'] = 'string|max:50|regex:/^[\p{L}\p{N}\s\-]+$/u';
        $customMessages['filters.brands.*.regex'] = 'Название бренда содержит недопустимые символы.';

        // Правила для categories (если есть)
        $rules['filters.categories'] = 'sometimes|array';
        $rules['filters.categories.*'] = 'string|max:50|regex:/^[\p{L}\p{N}\s\-]+$/u';
        $customMessages['filters.categories.*.regex'] = 'Название категории содержит недопустимые символы.';

        // Язык
        $rules['lang'] = 'required|string|max:3|regex:/^[a-z]{2,3}$/';
        $customMessages['lang.regex'] = 'Недопустимый формат языка.';

        return Validator::make($request->all(), $rules, $customMessages);
    }

    public function search(Request $request)
    {

        //Используй только Meilisearch,
        //но реализуй два режима отображения (а не два режима поиска).
        //Это даст тебе и скорость, и логичное поведение для пользователя.



        dd('Запрос ок!');


        //$query = trim($request->get('q', ''));
        //if (strlen($query) < 4) {
        //    return $this->shortSearch($query);
        //}
        //return $this->fullSearch($query);
    }

    private function shortSearch(string $query)
    {
        // Поиск в Meilisearch — только названия
        //$results = $this->meilisearch->search($query, [
        //    'attributesToRetrieve' => ['title'],
        //    'limit' => 10,
        //]);

        //return response()->json([
        //    'type' => 'short',
        //    'items' => $results['hits'],
        //]);
    }

    private function fullSearch(string $query)
    {
        // Полноценный поиск
        //$results = $this->meilisearch->search($query, [
        //    'attributesToRetrieve' => ['title', 'price', 'image', 'shop_name'],
        //    'limit' => 20,
        //]);

        //return response()->json([
        //    'type' => 'full',
        //    'items' => $results['hits'],
        //]);
    }







   // ---------------------- Старое ----------------------------------------------






    // Запрос от 1 до 3 символов первичный
	public function search_input_lik55555555555555555555555(Request $request) : JsonResponse {
        //Используй только Meilisearch,
        //но реализуй два режима отображения (а не два режима поиска).
        //Это даст тебе и скорость, и логичное поведение для пользователя.



        dd('Запрос ок!');
        dd('Запрос ок!');









        // Проверка пути запроса/маршрута
        if (!$request->is('searchl')) {
            return app('apiResponse')(
				[],
				'Page not found - SSC_001',
				'error',
				404
			);
        }

		$validator = $this->validateSearchRequest(
			$request,   // Передаем запрос
			3,          // Передаем лимит - максимально кол-во символов обрабатывает данный метод
		    1           // Передаем лимит - минимальное кол-во символов чтобы метод отработал
		);

        if ($validator->fails()) {
            // При ошибке валидации
			return app('apiResponse')(
				[],
				$validator->errors(),
				'error',
				422
			);
        }

		// Очищаем запрос от HTML и опасных символов
        $query = strip_tags($request->input('q'));
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

		// Метод отработает и вернет в JsonResponse
		return $this->search->search_input_like(
			$query,
		    $request->input('filters', []),
			$request->input('lang', app('lang')->getLocale()),
		);
	}

	// Поиск - Полноценный поиск (от 4+ символа) — FULLTEXT
	public function search_input_fulltext5555555555555555555555555555555(Request $request) : JsonResponse {

		// Проверка пути запроса/маршрута
		if (!$request->is('searchf')) {
            return app('apiResponse')(
				[],
				'Page not found - SSC_002',
				'error',
				404
			);
        }

		$validator = $this->validateSearchRequest(
			$request,   // Передаем запрос
			50,         // Передаем лимит максимально символов обрабатывает данный метод
		    4           // Передаем лимит минимальное кол-во символов чтобы метод отработал
		);

		if ($validator->fails()) {
            // При ошибке валидации
			return app('apiResponse')(
				[],
				$validator->errors(),
				'error',
				422
			);
        }

		// Очищаем запрос от HTML и опасных символов
        $query = strip_tags($request->input('q'));
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

		// Метод отработает и вернет в JsonResponse
		return $this->search->search_input_fulltext(
			$query,
		    $request->input('filters', []),
			$request->input('lang', app('lang')->getLocale()),
		);
	}
}
