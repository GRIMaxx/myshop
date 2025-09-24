<?php
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
 *
 **/
namespace App\Services\Setting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Contracts\Cache\CacheServiceInterface;
use App\Contracts\Setting\SettingServiceInterface;

class SettingService implements SettingServiceInterface
{
    protected string $cacheKey = 'settings';  // Имя сервиса или группы (список имен в кеше --> PREFIXES)

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
     public function addSetting(string $key,mixed $value,int $group,string $type = 'string',?string $description = null,string $environment = 'production',?int $updatedBy = null,?string $lang= null): void
     {
        /** Кеш (имееться в виду 2 кеша 1-в рамках запроса,2-Redis)
         *  Чтобы найти!
         *  Перед каждым созданием нового конфига считуем :
         *
         *  key - Уникальный ключ, по этому ключу будем в будущем конретно получать настройки
         *  group - Номер группы, по номеру группы в будушем будем определять где искать в каком файле из четырех груп
         *  type - Тип данных которые храняться под этим ключом
         *  is_active - Активна настройка да нет
         *
         *  Сам кеш для этих даных можно хранить в масиве напрмер [key => group] или в качестве строки 'key:group'
         *  потом буду определяться пока как идея.
        **/

         // Проверка допустимых групп
        $allowedGroups = config('settings.group_configs'); // (1=config, 2=lang, 3=db,...)
        if (!in_array($group, $allowedGroups, true)) {
            Log::error("Указана некорректная группа при добавлении настройки", [
                'key'         => $key,
                'group'       => $group
            ]);
            throw new \InvalidArgumentException("Invalid group '{$group}'. Allowed values: 1=config, 2=lang, 3=db");
        }

        // Для отката делаем все в DB::transaction
        DB::transaction(function () use (
            $key, $value, $group, $type, $description, $environment, $updatedBy, $lang
        ) {

            // Проверка на уникальность - если ключ существует выходим + логирование + ошибка
            if (DB::table('settings_registry')->where('key', $key)->exists()) {
                Log::warning("Попытка добавить дубликат ключа '{$key}'", [
                    'key'         => $key,        // Ключ
                    'group'       => $group,      // Группа
                ]);
                throw new \Exception("Setting with key '{$key}' already exists");
            }

            // Создаём запись в settings_registry (это основная таблица справочник)
            $registryId = DB::table('settings_registry')->insertGetId([
                'key'            => $key,               // Уникальный ключ
                'group'          => $group,             // (1=config, 2=lang, 3=db,...)
                'type'           => $type,              // Тип данных (string, int, bool, json и т.д.)
                'description'    => $description,       // Описание ключа (чисто документация/админка)
                'environment'    => $environment,       // Разделение по окружениям (dev/stage/prod)
                'is_active'      => true,               // Флаги состояния: иногда удобно хранить явно
                'is_locked'      => false,              // Опционально — locked: чтобы защитить часть настроек от случайного изменения в админке.
                'updated_by'     => $updatedBy,         // Автор изменений: в реальном маркетплейсе важно знать, кто поменял настройку.
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Проверка что insertGetId вернул id
            if (!$registryId) {
                Log::error("Ошибка insertGetId — ID не вернулся", [
                    'key'   => $key,
                    'group' => $group,
                ]);
                throw new \RuntimeException("Failed to insert setting '{$key}' into settings_registry");
            }

            // Сохраняем значение по группе
            match ($group) {
                1 => $this->saveToConfig($key, $value),
                2 => $this->saveToLang($registryId, $key, $value, $lang),
                3 => $this->saveToDb($registryId, $value),
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
    protected function saveToConfig(
        string $key,                   // Уникальный ключ он дублируеться в таблице settings_registr.key
        mixed $value
    ): void {

        // !Добавить кеш (он должен быть отдельно для каждой группы чтобы при каждом изменении дергать ту группу в которой и были изменения а не все 3)

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

    // Короче метод запишет компакно но не удобно читать глазами а это всеже конфиг по этому зделан новый ниже метод
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
        Сохранить перевод в resources/lang/{locale}/settings/general.php

        Пример что должно получиться:
        return [
          'name_site' => 'Морковь',
        ];
    */
    protected function saveToLang(
        int $registryId,               // ID под которым в таблице settings_registry краниться ключ от конфигурации
        string $key,                   // Уникальный ключ он дублируеться в таблице settings_registr.key
        mixed $value,                  // Значение (объекты -> массив) !запрещаем ресурсы/closures
        string $lang = null,           // Язык перевода
    ): void {

        // !Добавить кеш (он должен быть отдельно для каждой группы чтобы при каждом изменении дергать ту группу в которой и были изменения а не все 3)

        try {

            // Если язык не передан установить по умолчанию
            $lang = $lang ?? config('settings.lang_trs');

            // Список поддерживаемых локалей
            $locales = config('settings.supported_locales');

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
            Log::debug("Подключаю lang файл", ['realpath' => realpath($path)]);

            Log::info("Setting '{$key}' saved to lang file [{$lang}/settings/general.php]", [
                'registry_id' => $registryId,
                'lang' => $lang,
                'value' => $value,
            ]);

        } catch (\Throwable $e) {
            Log::error("Failed to save setting '{$key}' to lang", [
                'registry_id' => $registryId,
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
    protected function saveToDb(int $registryId, mixed $value): void
    {

        // !Добавить кеш (он должен быть отдельно для каждой группы чтобы при каждом изменении дергать ту группу в которой и были изменения а не все 3)

        try {
            DB::transaction(function () use ($registryId, $value) {

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

                // Для тестов логируем успешное сохранения
                Log::info("saveToDb: setting saved", [
                    'registry_id' => $registryId,
                    'setting_id'  => $id,
                    'value'       => $value,
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



























}
