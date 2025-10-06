<?php
/**
	Таблица для хранения фильтров для поиска

	Переводы используют : https://github.com/spatie/laravel-translation-loader/

    meilisearch - Для поиска используеться

    На момент создания таблиц - максимально собраные даные которые используют фильтры:
	'имя фильтра в качестве ключа' => [         Уникальные ключи фильтров - Варианты: (countries, promo, price, brands, categories, search_in rating_reviews availability_delivery)
		'type'               	=> '',    		string 	- NOT NULL - Варианты: (toggle_buttons, range_slider, checkbox_list, cat_checkbox_list, radiobox_list)
		'visible'            	=> true,    	bool   	- NULL
		'searchable'         	=> true,    	bool   	- NULL
		'activation' 	     	=> '',          string 	- NULL - Варианты: (auto, manual, first)
		'show_popular_first' 	=> true,    	bool   	- NULL
		'show_popular_group' 	=> true,    	bool   	- NULL
		'show_grouped_group' 	=> true,    	bool   	- NULL
		'show_ungrouped_group' 	=> true,    	bool   	- NULL
		'show_icon' 		   	=> true,    	bool   	- NULL
		'grouping' => [									- NULL
			'Popular' => ['nike', 'adidas']     		- NULL
		],
		'options' => [									- NOT NULL

			// Только для фильтра цена
			'startMin' 		=> 0,						- NULL или DEFAULT 0
			'startMax' 		=> 50000000,				- NULL или DEFAULT 0
			'min'   		=> 0,						- NULL или DEFAULT 0
			'max'   		=> 50000000,				- NULL или DEFAULT 0
			'step'  		=> 100,						- NULL или DEFAULT 0
			'tooltipPrefix' => "₽",				string 	- NULL
			'tooltipSuffix' => '',				string 	- NULL
			'unit'  		=> "",				string 	- NULL

			'Ключ элемента фильтра' => [                - NOT NULL (если есть)
				'default' => false,				bool   	- NULL
				'count'   => 234,				   		- NULL
				'popular' => false,				bool   	- NULL
				'icon'    => '',				string 	- NULL
				'color'   => '',				string 	- NULL
				'metadata' => [							- NULL
					'min_rating' => 4,					- NULL
				],

				Бро тут нужно как-то найти решения
				как получать при помощи __('Ukraine') переводы

				'translations' => [						- NULL
					'en' => 'Ukraine',			string 	- NULL
					'ru' => 'Украина',
				],
			],
		]
	],
**/
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
		// Основные настройки каждого фильтра
		Schema::create('filters', function (Blueprint $table) {

			// Уникальные ключи фильтров - Варианты: (countries, promo, price, brands, categories, search_in rating_reviews availability_delivery)
			$table->string('key', 100)->primary();
            // Тип фильтра
			$table->enum('type', ['toggle_buttons','range_slider', 'checkbox_list','cat_checkbox_list','radiobox_list'])->nullable(false);
            // Аактивация
			// auto   	- автоматически активирует все default: true фильтры
			// manual 	- не активирует ничего по умолчанию (оставит как по умолчанию здесь стоит тоесть если false так клиент и получит)
			// first  	- активирует первый доступный фильтр в группе
            $table->enum('activation', ['manual', 'auto', 'first'])->nullable();
            // Показывать ли всю группу (Группы с visible: false полностью исключаются из обработки - по умолчаниб true даже если удалить строку..)
			$table->boolean('visible')->default(true)->index(); // Часто фильтруем по visible

			// Сортировка фильтров
            // Если у всех 0 сортирует по id
            $table->unsignedInteger('sort_order')->default(0)->index();

            // -- Флаги

			// Добавляем возможность поиска Включить поиск среди элементов
			$table->boolean('searchable')->default(false)->index(); // Часто используем в WHERE
			// Показывать популярные первыми
			$table->boolean('show_popular_first')->default(false);
            // Показывать список с популярными элементами
		    $table->boolean('show_popular_group')->default(false);
            // Показывать список с групированными элементами
			$table->boolean('show_grouped_group')->default(false);
            // Показывать список с негруппированные элементы
			$table->boolean('show_ungrouped_group')->default(false);
            // Показывать иконку если есть или заменять если ее нет или скрыть полностью
			$table->boolean('show_icon')->default(false);

			// Правила групировки элементов в виде массива
			$table->json('grouping')->nullable();

			$table->timestamps();

			// Индекс для часто используемых полей вместе
            $table->index(['visible', 'searchable'], 'filters_visibility_searchable_index');
			$table->index(['key', 'visible'], 'filters_key_visible_index');
        });

        // варианты для каждого фильтра
		Schema::create('filter_options', function (Blueprint $table) {
            $table->id();											// unsignedBigInteger для связи с переводами

            $table->string('filter_key', 100)->index(); 			// Часто JOIN по этому полю
            $table->string('option_key', 100); 						// (sale, valentino и т.д.) Часто ищем конкретные опции

            // Основные параметры
            $table->boolean('default')->default(false)->index(); 	// Для быстрого поиска дефолтных
            $table->integer('count')->default(0)->index(); 			// Для сортировки по популярности
            $table->boolean('popular')->default(false)->index(); 	// Часто фильтруем популярные
            $table->string('icon', 191)->nullable();
            $table->string('color', 30)->nullable();

            // Сортировка опцый фильтра
            // Если у всех 0 сортирует по id
            $table->unsignedInteger('sort_order')->default(0)->index();

            // Параметры для range_slider (Спец даные для фильтра цена)
            $table->integer('start_min')->default(0);
            $table->integer('start_max')->default(50000000);
            $table->integer('min')->default(0);
            $table->integer('max')->default(50000000);
            $table->integer('step')->default(100);
            $table->string('tooltip_prefix', 10)->default('₽');
            $table->string('tooltip_suffix', 10)->default('');
            $table->string('unit', 100)->nullable(); //->default('');

            // Метаданные
            $table->json('metadata')->nullable();

            // Связи
            $table->foreign('filter_key')
                  ->references('key')
                  ->on('filters')
                  ->onDelete('cascade');

            // Составной уникальный индекс
            $table->unique(['filter_key', 'option_key'], 'filter_option_unique');

			// Составной индекс для часто используемых запросов
            $table->index(['filter_key', 'popular', 'count'], 'filter_options_popularity_index');

			$table->timestamps();
        });

		/** Перевод **/
		Schema::create('filter_option_translations', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedBigInteger('filter_option_id'); 			// соответствует ->id() в основной таблице
			$table->string('locale', 10)->index(); 						// или просто 'locale'

			// Переводимые поля
			$table->string('name', 150)->default('');
			$table->text('description')->nullable();

			// Уникальный индекс
			$table->unique(['filter_option_id', 'locale'], 'filter_option_translation_unique');

			$table->index('filter_option_id', 'filter_option_id_index');

			// Внешний ключ
			$table->foreign('filter_option_id')
				  ->references('id')
				  ->on('filter_options')
				  ->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_option_translations');
        Schema::dropIfExists('filter_options');
        Schema::dropIfExists('filters');
    }
};
