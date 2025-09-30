<?php
/**
 * Сохраненные даные:
 *
 * 1 Группа:
 *  theme_page
 *  pwa
 *
 * 2 Группа:
 *  site_name_en, site_name_ru
 *  meta_description_en, meta_description_ru
 *  meta_keywords_en, meta_keywords_ru
 *
 *
 *
 * 3 Группа
 *
 *
 *
 **/
//----------------------------------------------------------------------------------
/**
 * Обработка конфигурационных данных.
 *
 * Места хранения данных для работы сайта
 *
 * 1 группа - .\storage\settings.php                - тех. параметры и ссылки на ключи. (Здесь строго храним Статические которые очень редко меняються)
 * 2 группа - .\lang\{locale}\settings\settings.php - тексты, которые напрямую связаны с конфигурацией сайта.
 * 3 группа - Бд и таблица - settings               - динамических/пользовательских настроек.
 * 4 группа - .\config\settings.php                 - Эта група предназначена для хранения смешаных даных dafsult
 *
 * Основные методы:
 *      addSetting()        - Установить новые конфиг данные
 *      get()               - Получить данные
 *      updateSetting()     - Обновить даные строго существующего ключа + если есть локаль
 *      deleteSettingCache()- Удалить любую запись полностью из системы
 * Примеры использования или тесты (тесты пройдены 100%)
 *
 * $this->setting->addSetting('site_name2', 'MyShop-2', 1);
 * $this->setting->addSetting('autoren', 'Роман-en', 2, 'string','en');
 * $this->setting->addSetting('support_email-2', 'support@myshop.com', 3);
 * dd($this->setting->get('autor','Тест','ru'));
 * $this->setting->updateSetting('autor', 'GX-1234');
 * dd($this->setting->deleteSettingCache('support_email-1',));
 **/
namespace App\Services\Setting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Contracts\Cache\CacheServiceInterface;
use App\Contracts\Setting\SettingServiceInterface;

class SettingService implements SettingServiceInterface
{
    private const CACHE_SERVICE = 'settings'; // Имя сервиса(SettingService)/группы (список имен в кеше --> PREFIXES)

    private const CACHE_META    = 'meta';     // Тип данных (Ключ который гранит данные) (список типов в кеше --> TYPEPREFIXES)
    private const CACHE_DATA    = 'data';     // Тип данных (Для групп у которых ключ хранит данные группы) (список типов в кеше --> TYPEPREFIXES)

    private const C_CONFIG      = 'config';   // Ключ для группы (.\storage\settings.php)
    private const C_LANG        = 'lang';     // Ключ для группы (.\lang\{locale}\settings\settings.php)
    private const C_DB          = 'db';       // Ключ для группы (БД)
    private const C_CONFIG_DEF  = 'defaul';   // Ключ по умолчанию на случай збоя.

    private const C_TTL_META     = 86400;      // Время жизни ключа с даними
    private const C_TTL_DATA     = 86400;      // Время жизни Данных ключа

    /**
     * Времменый кеш в рамках запроса.
     * Request Cache (in-memory, внутри PHP-запроса)
     **/
    private static array $requestCache = [];
    public function __construct(
        private CacheServiceInterface $cache
    ) {}

    /**
     * Добавить новую настройку.
     * ----------------------------------------------------------------------------
     * Группы:
     * 1 = config (.\storage\settings.php)                 - Используется для технических параметров приложения.
     * 2 = lang   (.\lang\{locale}\settings\general.php)   - Используется для переводов (строки интерфейса).
     * 3 = db     (settings table)                         - Используется для динамических/пользовательских настроек.
     *
     * Важно! - правильное использования метода в рамках запроса только 1 метод если 2 и больше
     * перезапишет значения на последнее в последнем методе
     * Пример
     * $this->setting->addSetting('site_name1', 'MyShop', 1);      <-- эти даные будут стерты
     * $this->setting->addSetting('items_per_page', 20, 1, 'int'); <-- сотрет даные с ключем site_name1 и установит из этого метода и с этим ключем
     * ----------------------------------------------------------------------------
     * Допустимые типы Группа 1:
     *
     * string (например, 'site_name' => 'MyShop')
     * int ('items_per_page' => 20)
     * bool ('debug' => true)
     * array (например, 'currencies' => ['USD', 'EUR'])
     * не стоит хранить object — конфиг читается при загрузке приложения, там должны быть простые структуры.
     *
     * Примеры использования:
     *
     * $service->addSetting('site_name', 'MyShop', 1);                        - string
     * $service->addSetting('items_per_page', 20, 1, 'int');                  - int
     * $service->addSetting('debug_mode', true, 1, 'bool');                   - bool
     * $service->addSetting('currencies', ['USD', 'EUR', 'UAH'], 1, 'array'); - array
     * -------------------------------------------------------------------------------------
     * Допустимые типы Группа 2:
     *
     * string (99% случаев: 'site_name' => 'Магазин')
     * array (например, 'greeting' => ['morning' => 'Доброе утро', 'evening' => 'Добрый вечер'])
     * int/bool/object сюда не подходят, потому что система переводов в Laravel ожидает именно текст.
     * $lang обязателен (если не передан — дефолт en).
     *
     *  Примеры использования:
     *
     * -- string (основной вариант)
     *
     * $service->addSetting('site_name', 'Магазин', 2, 'string', lang: 'ru');
     * $service->addSetting('site_name', 'Shop', 2, 'string', lang: 'en');
     *
     * -- array (словарь переводов внутри одного ключа)
     *
     * $service->addSetting('greeting', [
     *      'morning' => 'Доброе утро',
     *      'evening' => 'Добрый вечер',
     * ], 2, 'array', lang: 'ru');
     *
     * $service->addSetting('greeting', [
     *      'morning' => 'Good morning',
     *      'evening' => 'Good evening',
     * ], 2, 'array', lang: 'en');
     *
     * Дополнительно для группы 2 если создаються масивы:
     * -вложенность поддерживает до 3 уровней (можно и 50 но это мусорка)
     * -если general.php пуст → создаст массив с твоим ключом,
     * -если в general.php уже есть другие настройки → они останутся, добавится только новый $key.
     *-------------------------------------------------------------------------------------------
     * Допустимые типы Группа 3:
     *
     * string, int, bool
     * array/object → сериализация в JSON (подходит для сложных структур)
     * resource/closure запрещены (не сериализуются).
     * $type критичен (string/int/bool/json и т.д.), потому что от него зависит валидация и каст при чтении.
     *
     * Примеры использования:
     *
     * $service->addSetting('support_email', 'support@myshop.com', 3);        - string
     * $service->addSetting('max_upload_size', 10485760, 3, 'int'); // 10 MB  - int
     * $service->addSetting('registration_enabled', false, 3, 'bool');        - bool
     *
     * array (сериализуется в JSON)
     * $service->addSetting('payment_methods', [
     *      'paypal' => true,
     *      'stripe' => false,
     * ], 3, 'json');
     *
     * object (сериализуется в JSON)
     * $service->addSetting('company_info', (object)[
     *      'name' => 'MyShop Inc.',
     *      'country' => 'USA',
     * ], 3, 'json');
     * ---
     * Аргументы:
     * @param string  $key         Уникальный ключ
     * @param mixed   $value       Значение (array/object → ok, запрещены ресурсы/closures)
     * @param int     $group       Группа (1=config, 2=lang, 3=db)
     * @param string  $type        Тип данных (string, int, bool, json и т.д.)
     * @param ?string $description Описание ключа (для админки/документации)
     * @param string  $environment Окружение (production/dev/stage)
     * @param ?int    $updatedBy   Кто добавил (id пользователя) Автор изменений: в реальном маркетплейсе важно знать, кто поменял настройку.
     * @param ?string $lang        Язык (только для группы 2, default = en)
    */
     public function addSetting(string $key,mixed $value,int $group,string $type = 'string', $lang = null, ?string $description = null,string $environment = 'production',?int $updatedBy = null): void
     {
         // Если язык не определен установить по умолчанию
         $lang = $lang ?? config('settings.lang_trs');

         // Проверка допустимых групп
         $allowedGroups = config('settings.group_configs'); // (1=config, 2=lang, 3=db,...)
         if (!in_array($group, $allowedGroups, true)) {
            Log::error("Указана некорректная группа при добавлении настройки", [
                'key'       => $key,
                'group'     => $group,
                'locale'    => $lang
            ]);
            throw new \InvalidArgumentException("Invalid group '{$group}'. Allowed values: 1=config, 2=lang, 3=db");
         }

         // Для отката делаем все в DB::transaction
         DB::transaction(function () use ($key, $value, $group, $type, $description, $environment, $updatedBy, $lang) {

            // Проверка на уникальность - если ключ существует, выходим + логирование + ошибка
            if (DB::table('settings_registry')->where('key', $key)->exists()) {
                Log::warning("Попытка добавить дубликат ключа '{$key}'", [
                    'key'       => $key,        // Ключ
                    'group'     => $group,      // Группа
                    'locale'    => $lang        // Язык если есть
                ]);
                throw new \Exception("Setting with key '{$key}' already exists");
            }

            // Создаём запись в settings_registry (это основная таблица справочник)
            $registryId = DB::table('settings_registry')->insertGetId([
                'key'            => $key,               // Уникальный ключ
                'group'          => $group,             // (1=config, 2=lang, 3=db,...)
                'type'           => $type,              // Тип данных (string, int, bool, json и т.д.)
                'locale'         => $lang,              // Для группы 2 спец поле на каом языке настройка(и)
                'description'    => $description,       // Описание ключа (чисто документация/админка)
                'environment'    => $environment,       // Разделение по окружениям (dev/stage/prod)
                'is_active'      => true,               // Флаги состояния: иногда удобно хранить явно
                'is_locked'      => false,              // Опционально — locked: чтобы защитить часть настроек от случайного изменения в админке.
                'updated_by'     => $updatedBy,         // Автор изменений: в реальном маркетплейсе важно знать, кто поменял настройку.
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Проверка что insertGetId вернул id - убилимся что записи созданы
            if (!$registryId) {
                Log::error("Ошибка insertGetId — ID не вернулся", [
                    'key'       => $key,
                    'group'     => $group,
                    'locale'    => $lang        // Язык если есть
                ]);
                throw new \RuntimeException("Failed to insert setting '{$key}' into settings_registry");
            }

            /**
             * Собрать данные для сохранения в кеше
             * group - Номер группы, по номеру группы в будушем будем определять где искать в каком файле из четырех груп
             * type - Тип данных которые храняться под этим ключом
             * is_active - Активна настройка да нет
             * is_locked - Флаг запрет на изменения или удаления настройки
             * locale - Язык настройки (спец поле для группы 2)
             */
            $meta_data = [                                  // Передаем данные для хранения
                'group'     => $group,
                'type'      => $type,
                'is_active' => true,
                'is_locked' => false,
                'locale'    => $lang
            ];

            /**
             * Установка кеша метаданых под уникальным ключем
             *
             * Пример ключа: meta:s_cfg:{$key} - (тестируем в Redis Insight)
             * Возвращает метод true/false
             **/
            $success = $this->cache->put(
                type:    self::CACHE_META,                  // Тип данных - meta
                key:     $key,                              // Уникальный ключ
                value:   $meta_data,                        // Собраные даные для сохранения
                ttl:     self::C_TTL_META,                  // Время жизни если null то год
                service: self::CACHE_SERVICE                // Имя сервиса исходя - setting
            );

            if (!$success) {
                Log::warning("Кеш метаданных не создан", [
                    'key'       => $key,
                    'group'     => $group,
                    'locale'    => $lang
                ]);
            }

            // Сохранить мета во временный кеш в рамках запроса (здесь он не работает это пример и тест)
            self::$requestCache[$key] = $meta_data;

            // Сохраняем значение по группе
            //
            match ($group) {
                1 => $this->saveToConfig($key, $value),
                2 => $this->saveToLang($key, $value, $lang),
                3 => $this->saveToDb($registryId, $value, $key),
                default => throw new \Exception("Unknown group {$group}"),
            };
        });
    }

    /**
        Сохранение в storage/settings.php (группа 1).
        Установить конфиг даные в .\storage\settings.php
        $registryId - id ключа в Таблице settings_registry
        $value      - Значения ключа
     */
    protected function saveToConfig(string $key, mixed $value): void {
        // Получить путь
        $path = storage_path('settings.php');
        $dir  = dirname($path);

        // Убедимся, что директория существует (обычно да)
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                Log::error("Не удалось создать директорию для config", ['dir' => $dir]);
                throw new \RuntimeException("Unable to create config directory: {$dir}");
            }
        }

        // Подготовим текущее содержимое (если файл существует и возвращает массив)
        $settings = [];
        if (file_exists($path)) {
            try {
                //opcache_invalidate($path, true); // сброс кеша
                $existing = include $path;
                if (is_array($existing)) {
                    $settings = $existing;
                } else {
                    Log::warning("Файл config/settings.php не вернул массив, будет перезаписан", ['path' => $path]);
                }
            } catch (\Throwable $e) {
                Log::error("Ошибка при подключении config/settings.php", ['path' => $path, 'error' => $e->getMessage()]);
                // не кидаем дальше — перезапишем файл
            }
        }

        // Нормализуем значение (объекты → массивы), запретим ресурсы/closure
        $value = $this->normalizeConfigValueForExport($value);

        // для теста
        //Log::info("Сохранить значения под ключем ----: ", ['key' => $key]);
        //Log::info("Сохранить значения ----: ", ['value' => $value]);

        // Сохраняем по ключу
        $settings[$key] = $value;

        // Формируем содержимое файла PHP
        $export = $this->exportArray($settings);

        $content = "<?php\n\nreturn " . $export . ";\n";

        // Атомарная запись: temp файл → rename
        $tmp = $path . '.tmp';

        if (file_put_contents($tmp, $content, LOCK_EX) === false) {
            Log::error("Не удалось записать временный файл конфига", ['tmp' => $tmp]);
            throw new \RuntimeException("Failed to write temp config file: {$tmp}");
        }

        // Переименовываем (атомарно заменяет оригинал на большинстве FS)
        if (!rename($tmp, $path)) {
            @unlink($tmp);
            Log::error("Не удалось переместить временный файл в финальный путь", ['tmp' => $tmp, 'path' => $path]);
            throw new \RuntimeException("Failed to install config file to: {$path}");
        }

        // Попытка выставить корректные права (не критичная)
        @chmod($path, 0644);

        // Собрать ключ кеш
        // готовый пример (смотр. в Redis Insight) : *:data:s_cfg:config:site_name
        $k_group = self::C_CONFIG . ":{$key}";

        /**
         * Установка данных настроек для охранения в основном кеше.
         * Возвращает метод true/false
         **/
        $success = $this->cache->put(
            type:    self::CACHE_DATA,      // Тип данных (data --> data)
            key:     $k_group,              // Уникальный ключ
            value:   $value,                // Даные для сохранения
            ttl:     self::C_TTL_DATA,      // Время жизни если null то год
            service: self::CACHE_SERVICE    // Имя сервиса (settings --> s_cfg)
        );

        if (!$success) {
            Log::warning("Кеш 1 группы не создан", ['key'=> $key]);
        }

        // Сохранить даные во временный кеш в рамках запроса (он здесь не работает - только для теста)
        self::$requestCache[$k_group] = $value;

        Log::info("Setting saved to config", ['key' => $key, 'path' => $path]);
    }

    /**
     * Привести значение к форме пригодной для var_export:
     * - объекты -> массив (через json encode/decode)
     * - запрещаем ресурсы/closures
     */
    protected function normalizeConfigValueForExport(mixed $value): mixed
    {
        if (is_resource($value) || $value instanceof \Closure) {
            Log::error("Попытка сохранить неподдерживаемый тип в config", ['type' => gettype($value)]);
            throw new \InvalidArgumentException("Unsupported value type for config");
        }

        if (is_object($value)) {
            // Простая и универсальная конвертация объекта -> array
            $decoded = json_decode(json_encode($value), true);
            // если не получилось — падаем
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Не удалось сериализовать объект для сохранения в config", ['error' => json_last_error_msg()]);
                throw new \RuntimeException("Failed to convert object to array for config storage");
            }
            return $decoded;
        }

        // массивы/скаляры — возвращаем как есть
        return $value;
    }

    /**
     * Короче метод запишет компакно но не удобно читать глазами а это всеже
     * конфиг по этому зделан новый ниже метод *
     */
    private function exportArray(array $array, int $level = 0): string
    {
        $indent     = str_repeat('    ', $level);
        $nextIndent = str_repeat('    ', $level + 1);

        $lines = [];
        foreach ($array as $key => $value) {
            $keyStr = is_int($key) ? $key : "'" . addslashes($key) . "'";
            if (is_array($value)) {
                $lines[] = $nextIndent . $keyStr . ' => ' . $this->exportArray($value, $level + 1);
            } else {
                $valStr  = var_export($value, true);
                $lines[] = $nextIndent . $keyStr . ' => ' . $valStr;
            }
        }

        return "[\n" . implode(",\n", $lines) . "\n" . $indent . "]";
    }

    /**
     * Сохранить перевод в resources/lang/{locale}/settings/general.php
     */
    protected function saveToLang(string $key, mixed $value, string $lang = null): void {
        try {
            // Если язык не определен установить по умолчанию
            $lang = $lang ?? config('settings.lang_trs');

            // Список поддерживаемых локалей
            $locales = config('settings.supported_locales');

            // Проверим еть в списке локаль если нет ошика (контроль записи)
            if (!in_array($lang, $locales, true)) {
                Log::error("Unsupported locale '{$lang}' for setting '{$key}'");
                throw new \Exception("Locale '{$lang}' is not supported");
            }

            // Путь до нужного файла (general.php - этот файл строго для этого сервиса)
            $path = lang_path("{$lang}/settings/general.php");
            $dir  = dirname($path);

            // Проверяем и создаём каталог если нужно
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                    throw new \RuntimeException("Failed to create directory: {$dir}");
                }
            }

            // Если файла нет – создаём пустой
            if (!file_exists($path)) {
                file_put_contents($path, "<?php\n\nreturn [];\n");
            }

            // Подгружаем текущие значения
            $settings = include $path;

            // Если вдруг кто-то руками положит не array в general.php, то $settings принудительно будет массивом.
            if (!is_array($settings)) {
                $settings = [];
            }

            // Обновляем или добавляем новое значение
            // Пример что должно получиться:
            // return [
            //	 'name_site' => 'Морковь',
            // ];
            $settings[$key] = $value;

            // Сохраняем файл обратно в синтаксисе PHP 5.4+
            $content = "<?php\n\nreturn " . $this->exportArray($settings) . ";\n";
            file_put_contents($path, $content);

            // для отладки
            if (file_put_contents($path, $content) === false) {
                Log::error("Не удалось записать файл настроек", ['path' => $path]);
            } else {
                Log::debug("Файл успешно записан", [
                    'path' => $path,
                    'content' => $content,
                ]);
            }

            // Для отладки куда пишет проверка!
            //Log::debug("Подключаю lang файл", ['realpath' => realpath($path)]);

            //Log::info("Setting '{$key}' saved to lang file [{$lang}/settings/general.php]", [
            //    'registry_id' => $registryId,
            //    'lang' => $lang,
            //    'value' => $value,
            //]);

            // Собрать ключ кеш
            // Если язык не указан ключ будет - *:en
            $k_group = self::C_LANG . ":{$key}:{$lang}";

            /**
             * Установка данных в виде настройки в кеш.
             * Возвращает метод true/false
             **/
            $success = $this->cache->put(
                type:    self::CACHE_DATA,      // Тип данных (data --> data)
                key:     $k_group,              // Прмер ключа в готовом виде под ним можно смотреть данные +- (все ключи разные) - data:s_cfg:lang:site_name:ru
                value:   $value,                // Данные для сохранения
                ttl:     self::C_TTL_DATA,      // Время жизни если null то год
                service: self::CACHE_SERVICE    // Имя сервиса (settings --> s_cfg)
            );

            if (!$success) {
                Log::warning("Кеш 1 группы не создан", ['key'=> $key, 'lang' => $lang]);
            }

            // Сохранить даные во временный кеш в рамках запроса (он здесь не работает - только для теста!ы)
            self::$requestCache[$k_group] = $value;

        } catch (\Throwable $e) {
            Log::error("Failed to save setting '{$key}' to lang", [
                'lang' => $lang,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
        Сохранение в таблицу settings (группа 3).

        $registryId - ID ключа в таблице settings_registry
        $value      - Значение (объекты -> массив) !запрещаем ресурсы/closures - это сырой вид
     */
    protected function saveToDb(int $registryId, mixed $value, string $key): void
    {
        try {
            DB::transaction(function () use ($registryId, $value, $key) {

                // Проверяем что registryId реально существует
                $exists = DB::table('settings_registry')->where('id', $registryId)->exists();
                if (!$exists) {
                    Log::error("saveToDb: registry_id '{$registryId}' not found");
                    throw new \Exception("Registry id '{$registryId}' does not exist");
                }

                // Добавляем запись
                $id = DB::table('settings')->insertGetId([
                    'registry_id' => $registryId,
                    'value'       => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                if (!$id) {
                    Log::error("saveToDb: failed to insert setting for registry_id '{$registryId}'", [
                        'value' => $value,
                    ]);
                    throw new \Exception("Failed to insert setting for registry_id '{$registryId}'");
                }

                // Собрать ключ кеш
                $k_group = self::C_DB . ":{$key}";

                /**
                 * Установка данных в кеш 3 группы
                 * Возвращает метод true/false
                 **/
                $success = $this->cache->put(
                    type:    self::CACHE_DATA,      // Тип данных (data --> data)
                    key:     $k_group,              // Прмер ключа в готовом виде под ним можно смотреть данные +- (все ключи разные) - data:s_cfg:bd:site_name
                    value:   $value,
                    ttl:     self::C_TTL_DATA,      // Время жизни если null то год
                    service: self::CACHE_SERVICE    // Имя сервиса (settings --> s_cfg)
                );

                if (!$success) {
                    Log::warning("Кеш 3 группы не создан", ['key'=> $key]);
                }

                // Сохранить даные во временный кеш в рамках запроса (он здесь не работает - только для теста!ы)
                self::$requestCache[$k_group] = $value;

                // Логируем успешное сохранения
                Log::info("saveToDb: setting saved", [
                    'registry_id' => $registryId,
                    'setting_id'  => $id
                ]);
            });
        } catch (\Throwable $e) {
            Log::error("saveToDb: exception", [
                'registry_id' => $registryId,
                'value'       => $value,
                'error'       => $e->getMessage(),
            ]);
            throw $e; // Пробрасываем дальше чтобы не скрыть ошибку
        }
    }

    // -- End -- конец методов для создания конфиг данных --------------------------------------------------------------

    /**
     * Получить настройки
     *
     * $requestCache - Кеш - его задача хранить все даные под ключем (в рамках запроса)

     * Redis (ключ) - его задача хранить ключ -
     * и номер группы + дополнительные даные.
     * То есть грубо копирует таблицу - settings_registry

     * Redis (данные (config/lang/db)) - его задача хранить даные ключей - то есть все типы даных под своим ключем -
     * с учетом, что один ключ может иметь несколько значений.
    **/
    public function get(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        // 1. Если язык не передан установить по умолчанию на тот случай если не найдет на нужном языке но есть по умолчанию
        $locale = $locale ?? config('settings.lang_trs');

        // 2. Redis (ключ) - Попытка получить мета-даные ключа
        // Если нет в кеше, бкдет попытка получить из БД - settings_registry
        // Пример: ['group', 'type', 'is_active', 'is_locked', 'locale']
        $meta = $this->redisGetKeyMeta($key, $locale);

        // 3. Мета-данные не получены, вернуть даные по умолчанию переданые в качестве параметров методу
        if (!$meta) {
            return $this->handleNotFound($default);
        }

        // 4.
        $value = $this->redisGetData($key, $meta, $locale, $default);

        // Если нет данных вернем по умолчанию или null
        if ($value === null) {
            return handleNotFound($default);
        }

        return $value;
    }

    /**
     * Получить метаданные ключа из Redis или settings_registry.
     *
     * Логика:
     *  1. Пробуем достать из Request Cache (in-memory, внутри одного запроса).
     *  2. Пробуем достать из Redis (основной кеш).
     *  3. Если нет — ищем в settings_registry (резерв и финальный вариант).
     *      4. Нет в settings_registry → лог + вернуть null.
     *      5. Есть, но is_active=0 или is_locked=1 → лог + вернуть null.
     *      6. Нашли валидный → записать в Redis и в Request Cache, вернуть.
     *  7. Если нашли в Redis — проверяем is_active / is_locked.
     *      8. Если не ок → лог + вернуть null.
     *      9. Если ок → вернуть.
     *
     * @param string $key         Уникальный ключ настройки.
     * @param string|null $locale Язык (используется только для lang-группы).
     * @return array|null         Ассоциативный массив с метаданными или null, если не найдено/заблокировано.
     */
    protected function redisGetKeyMeta(string $key, ?string $locale = null): ?array
    {
        try {

            // 1. Проверяем Request Cache (in-memory, в рамках PHP-запроса)
            if (isset(self::$requestCache[$key])) {
                return self::$requestCache[$key];
            }

            // 2. Пробуем получить из Redis
            $meta = $this->cache->get(
                self::CACHE_META,           // Тип данных
                $key,                           // Ключ
                self::CACHE_SERVICE       // Сервис (prefix)
            );

            // 3. Проверяем найденные мета-данные
            if (is_array($meta)) {
                if (($meta['is_active'] ?? 0) == 0 || ($meta['is_locked'] ?? 0) == 1) {
                    Log::warning("Key '{$key}' is inactive or locked (from Redis)", [
                        'key' => $key,
                    ]);
                    return null;
                }

                // Сохраняем в Request Cache - временный кеш
                self::$requestCache[$key] = $meta;

                return $meta;
            }

            // 4. Нет в Redis → достаём из БД
            $meta = DB::table('settings_registry')
                ->select(['group', 'type', 'is_active', 'is_locked', 'locale'])
                ->where('key', $key)
                ->first();

            // 5. Если ключ не найден в БД
            if (!$meta) {
                Log::error("Key '{$key}' not found in settings_registry", ['key' => $key]);
                return null;
            }

            // 6. Преобразуем в массив
            $meta = (array) $meta;

            // 7. Проверяем активность и блокировку
            if (($meta['is_active'] ?? 0) == 0 || ($meta['is_locked'] ?? 0) == 1) {
                Log::warning("Key '{$key}' is inactive or locked (from DB)", [
                    'key' => $key,
                ]);
                return null;
            }

            // 8. Собираем финальные данные для кеша
            $metaData = [
                'group'     => (int) $meta['group'],
                'type'      => (string) $meta['type'],
                'is_active' => (bool) $meta['is_active'],
                'is_locked' => (bool) $meta['is_locked'],
                'locale'    => $locale ?? ($meta['locale'] ?? null),
            ];

            // 9. Сохраняем в Redis (META cache)
            $this->cache->put(
                type:    self::CACHE_META,
                key:     $key,
                value:   $metaData,
                ttl:     self::C_TTL_META,
                service: self::CACHE_SERVICE
            );

            // 10. Сохраняем в Request Cache
            self::$requestCache[$key] = $metaData;

            // Возвращаем данные
            return $metaData;

        } catch (\Throwable $e) {
            Log::error("Error while getting meta for key '{$key}'", [
                'key'     => $key,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Получить данные настройки по ключу
     *
     * Алгоритм работы:
     *  1. Определяет группу данных (config/lang/db/system)
     *  2. Формирует уникальный ключ кеша
     *  3. Проверяет локальный кеш запроса (in-memory, self::$requestCache) - как для ключей и для данный
     *  4. Пробует получить данные из Redis (основной кеш) + как ключи так и данные
     *  5. Если не найдено — загружает данные из источника (storage/lang/db/config)
     *  6. При нахождении восстанавливает кеш (Redis + in-memory)
     *  7. Если не найдено нигде — выполняет fallback и возвращает default
     *
     * @param string      $key        Ключ настройки
     * @param mixed       $meta       Метаданные настройки (array|object)
     * @param string|null $locale     Локаль, если применимо (для lang-группы)
     * @param mixed       $default    Значение по умолчанию, если данных нет
     *
     * @return mixed Возвращает значение настройки или значение по умолчанию
     */
    protected function redisGetData(string $key, mixed $meta, string $locale = null, mixed $default): mixed
    {
        try {

            // 1. Определяем группу (1=config, 2=lang, 3=db, 4=system)
            $group = (int) (
            is_array($meta)
                ? ($meta['group'] ?? 4)
                : (is_object($meta) ? ($meta->group ?? 4) : 4)
            );

            // 2. Собрать ключ кеша
            $k_group = $this->makeGroupKey($group, $key, $locale);

            // 3. Проверяем локальный кеш (in-memory) внутри PHP-запросаах запроса
            if (isset(self::$requestCache[$k_group])) {
                return self::$requestCache[$k_group];
            }

            // 4. Найти и считать даные ключа (Данные получаем уже в исходном состоянии как они были установлены изначально!)
            $data = $this->cache->get(
                self::CACHE_DATA,                       // Тип данных
                $k_group,                                   // Ключ записи
                self::CACHE_SERVICE                   // Сервис-источник (PREFIXES)
            );

            // 5 Если есть даные вернуть
            if ($data !== null) {
                self::$requestCache[$k_group] = $data;      // Сохраним в рамках запроса
                return $data;
            }

            // 6. Первый вариант найти данные (настройки) по номеру группы
            // Загрузить из:
            //  1 группа storage/settings.php
            //  2 группа lang/{locale}/settings/general.php
            //  3 группа (settings + settings_registry)
            //  4 группа ./config/settings.php
            // Если ничего не найдет вернет по умолчанию - если есть
            $data = $this->loadFromGroup($group, $key, $locale, $default);

            // Если данные найдены востановим и вернем их
            if ($data !== null) {
                // Востанавливаем основной кеш
                $this->cache->put(self::CACHE_DATA, $k_group, $data, self::C_TTL_DATA, self::CACHE_SERVICE);
                // Востанавливаем для повторного значения в рамках запроса
                self::$requestCache[$k_group] = $data;
                // Вернуть
                return $data;
            }

            // 7. Второй вариант - Полный обход всех групп (confog/lang/bd/default) + 1 группа системная хранит по умолчанию данные
            // Порядок важен по этому в конфиге строго он установлен
            foreach (config('settings.group_configs') as $gp) {

                if ($gp === $group) {
                    continue; // эту групу уже пробовали в "3." пропустим ее с экономим ресурсы
                }

                // Отправиться в каждую групу и попытаться найти данные
                $data = $this->loadFromGroup($gp, $key, $locale, $default);

                // Если даные найдены
                if ($data !== null) {

                    // Востанавливаем основной кеш
                    $this->cache->put(self::CACHE_DATA, $k_group, $data, self::C_TTL_DATA, self::CACHE_SERVICE);

                    // Востаналиваем для повторного использования в рамках запроса
                    self::$requestCache[$k_group] = $data;

                    // Остановит цикл и вернет данные
                    return $data;
                }
            }

            // 8. Логируем отсутствие ключа
            Log::error("Key {$key} not found in any source", [
                'key'   => $key,
            ]);

            // 9. Возврат значения по умолчанию
            return $this->handleNotFound($default);
        } catch (\Throwable $e) {
            // На случай ошибки Redis или DB
            Log::error("Error retrieving key settings data {$key}: ", [
                'message'   => $e->getMessage(),
            ]);
            return $this->handleNotFound($default);
        }
    }

    /**
     * Собрать правильые ключи для всех кешей которые хранять настройки *
     */
    private function makeGroupKey(int $group, string $key, ?string $locale = null): string
    {
        return match ($group) {
            1 => self::C_CONFIG . ":{$key}",
            2 => self::C_LANG   . ":{$key}" . ($locale ? ":{$locale}" : ''),
            3 => self::C_DB     . ":{$key}",
            default => self::C_CONFIG_DEF . ":{$key}",
        };
    }

    /** Определяет и запускает поиск исходя из номера группы **/
    private function loadFromGroup(int $group, string $key, string $locale, mixed $default): mixed
    {
        return match ($group) {
            1 => $this->loadFromConfig($key),
            2 => $this->loadFromLang($key, $locale),
            3 => $this->loadFromDb($key),
            4 => $this->loadFromConfigDefault($key),
            default => $this->handleNotFound($default),
        };
    }

    /**
     * Загрузить из storage/settings.php
     */
    private function loadFromConfig(string $key): mixed
    {
        $file = storage_path('settings.php');
        if (!file_exists($file)) {
            return null;
        }
        $data = include $file;
        return $data[$key] ?? null;
    }

    /**
     * Загрузить из lang/{locale}/settings/general.php
     */
    private function loadFromLang(string $key, string $locale): mixed
    {
        // Найти по ключу --> "/var/www/myshop/lang/{$locale}/settings/general.php"
        $file = lang_path("{$locale}/settings/general.php");
        if (!file_exists($file)) {
           return null;
        }
        $data = include $file;
        return $data[$key] ?? null;
    }

    /**
     * Загрузить из базы (settings + settings_registry)
     */
    private function loadFromDb(string $key): mixed
    {
        $row = DB::table('settings')
            ->join('settings_registry', 'settings.registry_id', '=', 'settings_registry.id')
            ->where('settings_registry.key', $key)
            ->select('settings.value')
            ->orderByDesc('settings.id') // если вдруг есть дубликаты → берём последнюю
            ->first();

        return $row ? json_decode($row->value, true) : null;
    }

    /**
     * Фолбэк: config/settings.php
     */
    private function loadFromConfigDefault(string $key): mixed
    {
        return config("settings.{$key}") ?? null;
    }

    /** Вернуть даные по умолчанию переданые методом/null **/
    protected function handleNotFound(mixed $default = null): mixed {
        return $default;
    }

    // -- END -- Получения даных (настроек) ----------------------------------------------------------------------------

    /**
     * Обновить существующую настройку по её ключу.
     * Метод предназначен для - Супер-админа / системных процессов
     *
     * Для обычных админов можно собрать отдельный метод с :
     * if ($meta['is_locked'] === true) {
     *  throw new \RuntimeException("Эта настройка защищена и не может быть изменена.");
     * }
     *
     * Метод безопасно обновляет данные настройки (значение, тип, описание, флаги активности и блокировки,...),
     * автоматически определяя её группу (config, lang или db) из кеша или базы данных.
     *
     * Метод не будет обновлять даные если ключ не найден или ключ есть но другая локаль!
     * Это полностью предотвращает случайное обновление ключа другой локали (*:ru vs *:en).
     * Основные принципы работы:
     *  - Группа настройки определяется автоматически (её нельзя изменить вручную).
     *  - Сначала ищет метаданные ключа в Redis; если нет — загружает из БД.
     *  - Если ключ отсутствует в обоих источниках, обновление не выполняется.
     *  - Системная группа (4) защищена от изменений.
     *  - Все изменения выполняются в транзакции.
     *  - После успешного обновления данные пересохраняются в соответствующее хранилище:
     *      • group=1 → storage/settings.php
     *      • group=2 → lang/{locale}/settings/*.php
     *      • group=3 → таблица settings_registry (через saveToDb)
     *
     * @param  string       $key           Уникальный ключ настройки.
     * @param  mixed        $value         Новое значение настройки.
     * @param  string|null  $lang          Код языка (для локализованных настроек, group=2).
     * @param  string|null  $type          Тип значения (string, int, bool, json и т.д.), по умолчанию "string".
     * @param  bool|null    $is_active     Активна ли настройка (true — включена).
     * @param  bool|null    $is_locked     Заблокирована ли настройка (true — защищена от изменений в админке).
     * @param  string|null  $description   Описание настройки (для документации и интерфейсов).
     * @param  string|null  $environment   Окружение ("production", "staging", "local" и т.д.), по умолчанию "production".
     * @param  int|null     $updatedBy     ID пользователя, изменившего настройку (опционально).
     *
     * @return bool Возвращает true, если настройка успешно обновлена, иначе false.
     *
     * @throws \InvalidArgumentException Если указана недопустимая группа.
     * @throws \RuntimeException Если попытка изменить системную группу (4).
     * @throws \Exception При ошибках сохранения или валидации.
     */
    public function updateSetting(
        string $key,
        mixed $value,
        ?string $lang = null,
        ?string $type = 'string',
        ?bool $is_active = true,
        ?bool $is_locked = false,
        ?string $description = null,
        ?string $environment = 'production',
        ?int $updatedBy = null
    ): bool {

        // 1. Проверить, существует ли ключ в кеше
        // - Вернет гарантированно масив при условии что если ключ найден!
        //[
        //    "group"     => 1/2/3      - номер одной из групп
        //    "type"      => "string"   - тип даных которые храняться
        //    "is_active" => true
        //    "is_locked" => false
        //    "locale"    => "ru/en"
        //]
        $meta = $this->getOrRestoreRedis($key);

        // 2. Если ключ в кеше не найден
        if ($meta === null) {

            // 3. Проверить, существует ли ключ в БД с данными
            $meta = DB::table('settings_registry')
                ->select('id', 'group', 'type', 'is_active', 'is_locked', 'locale')
                ->where('key', $key)
                ->first();                      // Вернёт объект или null

            // 4. Если и в БД нет ключа и данных
            if (!$meta) {
                Log::error("Попытка обновления несуществующего ключа", [
                    'key'   => $key,
                ]);
                return false;
            }
        }

        // 3. Определяем группу (1=config, 2=lang, 3=db,...), с учетом от куда были полученны мета-данные из кеша или Бд
        $group = (int) (is_object($meta) ? $meta->group : ($meta['group'] ?? 0));

        // 4. Получить локаль при любых вариантов (object | array | null), чтобы небыло пустых полей
        // Если $meta объект — берём $meta->locale, если пусто — подставляем дефолт из конфига.
        // Если $meta массив — аналогично $meta['locale'].
        // Если $meta нет — сразу дефолт.
        // Приведение к (string) гарантирует, что даже пустой null станет строкой.
        $locale = (string) (
            is_object($meta)
                ? (!empty($meta->locale) ? $meta->locale : config('settings.lang_trs'))
                : (is_array($meta)
                    ? (!empty($meta['locale']) ? $meta['locale'] : config('settings.lang_trs'))
                    : config('settings.lang_trs'))
            );

        // 5. Отловить попытку - полностью предотвращает случайное обновление ключа другой локали (*:ru vs *:en).
        if ($lang !== null && $lang !== $locale && $group === 2) {
            Log::error("Указанная локаль не совпадает с локалью в мета-данных и её изменять нельзя!", [
                'key'   => $key,
                'group' => $group,
                'lang'  => $lang,
                'locale' => $locale,
            ]);
            throw new \InvalidArgumentException(
                sprintf(
                    "С указанной '%s' локалью не найден ключ, который есть с локалью '%s'.",
                    $lang,
                    $locale
                )
            );
        }

        $lang = $locale;     // Синхронизируем локали на случай если $lang == null

        // 6. Проверка допустимых групп
        // - Практически такой вариант не возможен, но на всякий пожарный (от взлома кеша).
        $allowedGroups = config('settings.group_configs');
        if (!in_array($group, $allowedGroups, true)) {
            Log::error("Указана некорректная группа при обновлении настроек", [
                'key'   => $key,
                'group' => $group
            ]);
            throw new \InvalidArgumentException("Invalid group '{$group}'. Allowed values: 1=config, 2=lang, 3=db");
        }

        // 7. Запрещаем системную группу (4) обновлять
        // - Практически такой вариант не возможен, но на всякий пожарный (от взлома кеша).
        if ($group === 4) {
            Log::warning("Попытка изменить системную группу настроек (group=4), доступ запрещён", [
                'key'   => $key,
                'group' => 4
            ]);
            throw new \RuntimeException("Изменение системной группы (4) запрещено");
        }

        // 8. Преобразуем meta в массив для удобства дальнейшей работы
        $meta = (array) $meta;

        // Обновления
        return DB::transaction(function () use (
            $key, $value, $type, $description, $environment, $lang, $is_active, $is_locked, $meta, $updatedBy, $group
        ) {

            /**  9. Собрать данные для сохранения в кеше
             * group - Номер группы, по номеру группы в будушем будем определять где искать в каком файле из четырех груп
             * type - Тип данных которые храняться под этим ключом
             * is_active - Активна настройка да нет
             * is_locked - Флаг запрет на изменения или удаления настройки
             * locale - Язык настройки (спец поле для группы 2)
             */
            $meta_data = [                                  // Передаем данные для хранения
                'group'     => (int) $group,
                'type'      => (string) $type,
                'is_active' => (bool) $is_active,
                'is_locked' => (bool) $is_locked,
                'locale'    => (string) $lang
            ];

            // 10. Обновления кеша ключей с мета-данными
            $success = $this->cache->put(
                type:    self::CACHE_META,
                key:     $key,
                value:   $meta_data,
                ttl:     self::C_TTL_META,
                service: self::CACHE_SERVICE
            );

            if (!$success) {
                Log::warning("Кеш метаданных не обновлён", ['key' => $key]);
            }

            // 11. Сохранить во временный кеш метаданые - (он здесь не работает это для теста!)
            self::$requestCache[$key] = $meta_data;

            // Извлекаем id
            $registryId = $meta['id'] ?? null;

            // 12. Обновить данные в определенной группее исходя из номера группы.
            // Новые значения попросту сотрут старые и запишет новые как ф файлах так и в БД
            // После обновления группа обновит временный кеш и основной для данных
            match ($group) {
                1 => $this->saveToConfig($key, $value),
                2 => $this->saveToLang($key, $value, $lang),
                3 => $this->saveToDb($registryId, $value, $key),
                default => throw new \Exception("Unknown group {$group}"),
            };

            // 10. Обновить запись в settings_registry (это основная таблица справочник)
            $updated = DB::table('settings_registry')
                ->where('key', $key)
                ->update([
                    'group'        => $group,
                    'type'         => $type,
                    'locale'       => $lang,
                    'description'  => $description,
                    'environment'  => $environment,
                    'is_active'    => $is_active,
                    'is_locked'    => $is_locked,
                    'updated_by'   => $updatedBy ?? null,
                    'updated_at'   => now(),
                ]);

            Log::info("Настройка обновлена", [
                'key'        => $key,
                'group'      => $group,
                'updated_by' => $updatedBy
            ]);

            return (bool) $updated;
        });
    }

    /**
     * Проверить, есть ли ключ в Redis.
     * Если есть — вернуть данные.
     * Если нет — вернуть null.
     */
    public function getOrRestoreRedis(string $key): ?array
    {
        // 1. Проверяем локальный кеш (in-memory) внутри PHP-запроса
        if (isset(self::$requestCache[$key])) {
            return self::$requestCache[$key];
        }

        // 2. Проверяем наличие ключа в основном Кеше для ключей и мета-дынных, если нет ернуть null
        if (!$this->cache->exists(self::CACHE_META, $key, self::CACHE_SERVICE)) {
            return null;
        }

        // 3. Получаем данные
        $data = $this->cache->get(self::CACHE_META, $key, self::CACHE_SERVICE);

        // 4. Проверяем структуру
        return is_array($data) ? $data : null;
    }

    // -- END -- Обновления даных (настроек) ----------------------------------------------------------------------------

    /**
     * Удалить любую настройку (включая сам ключ и все связанные данные).
     *
     * Только для супер-админа или системных операций!
     *
     * Действия:
     *  - Проверяет наличие ключа в кеше или БД.
     *  - Определяет группу (1=config, 2=lang, 3=db).
     *  - Удаляет:
     *      - данные из кеша (Redis),
     *      - временный кеш запроса,
     *      - данные из файлов / языковых файлов / таблицы settings,
     *      - сам ключ из settings_registry.
     *
     * @param string $key        Уникальный ключ настройки.
     * @param string|null $lang  Локаль (только для группы 2).
     *
     * @return bool
     *
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function deleteSettingCache(
        string $key,          // Уникальный ключ
        ?string $lang = null, // Локаль - удалить в конкретный ключ с конкретным переводом
    ): bool {

        // 1. Проверить, существует ли ключ в кеше
        // - Вернет гарантированно масив при условии что если ключ найден!
        //[
        //    "group"     => 1/2/3      - номер одной из групп
        //    "type"      => "string"   - тип даных которые храняться
        //    "is_active" => true
        //    "is_locked" => false
        //    "locale"    => "ru/en"
        //]
        $meta = $this->getOrRestoreRedis($key);

        // 2. Если ключ в кеше не найден
        if ($meta === null) {

            // 3. Проверить, существует ли ключ в БД с данными
            $meta = DB::table('settings_registry')
                ->select('id', 'group', 'type', 'is_active', 'is_locked', 'locale')
                ->where('key', $key)
                ->first();                      // Вернёт объект или null

            // 4. Если и в БД нет ключа и данных
            if (!$meta) {
                Log::error("Попытка обновления несуществующего ключа", [
                    'key'   => $key,
                ]);
                return false;
            }
        }

        // 5. Определяем группу (1=config, 2=lang, 3=db,...), с учетом от куда были полученны мета-данные из кеша или Бд
        $group = (int) (is_object($meta) ? $meta->group : ($meta['group'] ?? 0));

        // 6. Получить локаль при любых вариантов (object | array | null), чтобы небыло пустых полей
        // Если $meta объект — берём $meta->locale, если пусто — подставляем дефолт из конфига.
        // Если $meta массив — аналогично $meta['locale'].
        // Если $meta нет — сразу дефолт.
        // Приведение к (string) гарантирует, что даже пустой null станет строкой.
        $locale = (string) (
        is_object($meta)
            ? (!empty($meta->locale) ? $meta->locale : config('settings.lang_trs'))
            : (is_array($meta)
            ? (!empty($meta['locale']) ? $meta['locale'] : config('settings.lang_trs'))
            : config('settings.lang_trs'))
        );

        // 7. Отловить попытку - полностью предотвращает случайное обновление ключа другой локали (*:ru vs *:en).
        // Другими словами $lang === $locale - всегда должны совпадать
        if ($group === 2 && $lang !== null && $lang !== $locale) {
            Log::error("Указанная локаль не совпадает с локалью в мета-данных и её изменять нельзя!", [
                'key'   => $key,
                'group' => $group,
                'lang'  => $lang,
                'locale' => $locale,
            ]);
            throw new \InvalidArgumentException(
                sprintf(
                    "С указанной '%s' локалью не найден ключ, который есть с локалью '%s'.",
                    $lang,
                    $locale
                )
            );
        }

        $lang = $locale;   // Синхронизируем данные

        // 8. Проверка допустимых групп
        // - Практически такой вариант не возможен, но на всякий пожарный (от взлома кеша).
        $allowedGroups = config('settings.group_configs', [1, 2, 3]);
        if (!in_array($group, $allowedGroups, true)) {
            Log::error("Указана некорректная группа при обновлении настроек", [
                'key'   => $key,
                'group' => $group
            ]);
            throw new \InvalidArgumentException("Invalid group '{$group}'. Allowed values: 1=config, 2=lang, 3=db");
        }

        // 9. Запрещаем системную группу (4) обновлять
        // - Практически такой вариант не возможен, но на всякий пожарный (от взлома кеша).
        if ($group === 4) {
            Log::warning("Попытка изменить системную группу настроек (group=4), доступ запрещён", [
                'key'   => $key,
                'group' => 4
            ]);
            throw new \RuntimeException("Изменение системной группы (4) запрещено");
        }

        // 10. Преобразуем meta в массив для удобства дальнейшей работы
        $meta = (array) $meta;
        $registryId = $meta['id'] ?? null;

        // Обновления
        return DB::transaction(function () use ($group, $key, $lang, $registryId) {

            // 11. Удалить данные из группы
            match ($group) {
                1 => $this->deleteFromConfig($key),
                2 => $this->deleteFromLang($key, $lang),
                3 => $this->deleteFromDb($registryId, $key),
                default => throw new \RuntimeException("Unknown group {$group}"),
            };

            try {
                // 12. Удаления из кеша ключ и метаданные
                $this->cache->deleteOrFail(
                    type: self::CACHE_META,
                    key: $key,
                    service: self::CACHE_SERVICE
                );

                $key_data = $this->makeGroupKey($group, $key, $lang);

                // 13. Удаления из основного кеша с данными
                $this->cache->deleteOrFail(
                    type: self::CACHE_DATA,
                    key: $key_data,
                    service: self::CACHE_SERVICE
                );
                unset(self::$requestCache[$key]);           // Удалить из кеша ключи с мета-данными
                unset(self::$requestCache[$key_data]);      // Удалить из кеша ключи с настройками
            } catch (\Throwable $e) {
                Log::error("Ошибка при удалении кеша настройки", [
                    'key' => $key,
                    'group' => $group,
                    'error' => $e->getMessage(),
                ]);
            }

            // 14. Удалить сам ключ из settings_registry
            DB::table('settings_registry')->where('key', $key)->delete();

            Log::info("Настройка полностью удалена", [
                'key' => $key,
                'group' => $group,
            ]);

            return true;
        });
    }

    /** Удаление из config (storage/settings.php) */
    private function deleteFromConfig(string $key): void
    {
        $path = storage_path('settings.php');

        if (!file_exists($path)) {
            Log::warning("Файл storage/settings.php не найден при удалении ключа", ['key' => $key]);
            return;
        }

        try {
            // 1. Подключаем файл как массив
            $settings = include $path;

            if (!is_array($settings)) {
                Log::error("Некорректный формат storage/settings.php (ожидался массив)");
                return;
            }

            // 2. Проверяем наличие ключа
            if (!array_key_exists($key, $settings)) {
                Log::notice("Ключ '{$key}' не найден в storage/settings.php");
                return;
            }

            // 3. Удаляем ключ
            unset($settings[$key]);

            // 4. Формируем красиво оформленный PHP-код через exportArray()
            $content = "<?php\n\nreturn " . $this->exportArray($settings) . ";\n";

            // 5. Перезаписываем файл
            file_put_contents($path, $content);

            Log::info("Ключ '{$key}' успешно удалён из storage/settings.php");
        } catch (\Throwable $e) {
            Log::error("Ошибка при удалении ключа из storage/settings.php", [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Удаление из lang/{locale}/settings */
    private function deleteFromLang(string $key, ?string $lang): void
    {
        $lang = $lang ?: config('settings.lang_trs', 'en');
        $dir = lang_path("{$lang}/settings");

        if (!is_dir($dir)) {
            Log::warning("Директория языковых файлов не найдена", ['lang' => $lang, 'key' => $key]);
            return;
        }

        $deleted = false;

        try {
            // 1. Перебираем все файлы внутри lang/{locale}/settings/
            foreach (glob($dir . '/*.php') as $file) {
                $data = include $file;

                if (!is_array($data) || !array_key_exists($key, $data)) {
                    continue;
                }

                // 2. Удаляем ключ
                unset($data[$key]);

                // 3. Формируем красиво оформленный PHP-код через exportArray()
                $content = "<?php\n\nreturn " . $this->exportArray($data) . ";\n";

                // 4. Перезаписываем файл
                file_put_contents($file, $content);

                Log::info("Ключ '{$key}' удалён из языкового файла", [
                    'lang' => $lang,
                    'file' => basename($file),
                ]);

                $deleted = true;
            }

            // 5. Если не найден ни в одном файле
            if (!$deleted) {
                Log::notice("Ключ '{$key}' не найден ни в одном языковом файле для локали '{$lang}'");
            }
        } catch (\Throwable $e) {
            Log::error("Ошибка при удалении ключа из языковых файлов", [
                'lang' => $lang,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Удаление из базы (settings, settings_registry) */
    private function deleteFromDb(?int $registryId, string $key): void
    {
        if ($registryId) {
            DB::table('settings')->where('registry_id', $registryId)->delete();
        }
        Log::debug("Удалена настройка из БД", ['key' => $key]);
    }
}
