<?php
/**

    Основные методы
    addSetting()


 **/
namespace App\Services\Setting;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Contracts\Cache\CacheServiceInterface;

//use Illuminate\Support\Facades\Config;

class SettingService
{
    protected string $cacheKey = 'settings';  // Имя сервиса или группы (список имен в кеше --> PREFIXES)

    public function __construct(
        private CacheServiceInterface $cache
    ) {}

    /**
        Добавить новую настройку.

        Группы:
        1 = config (.\config\settings.php)
        2 = lang   (.\lang\{locale}\settings\general.php)
        3 = db     (settings table)
        ---
        Примеры использования:

        Группа 1 (config) → сохранит в config/settings.php
        $service->addSetting('site_name', 'MyShop', 1);

        Группа 2 (lang) → сохранит перевод в lang/ru/settings/general.php
        $service->addSetting('site_name', 'Магазин', 2, lang: 'ru');

        Группа 2 (lang) → без указания языка, дефолт = en
        $service->addSetting('site_name', 'Shop', 2);

        Группа 3 (db) → сохранит в таблицу settings
        $service->addSetting('items_per_page', 20, 3, 'int');
        ---
        Аргументы:
        @param string  $key         Уникальный ключ
        @param mixed   $value       Значение (array/object → ok, запрещены ресурсы/closures)
        @param int     $group       Группа (1=config, 2=lang, 3=db)
        @param string  $type        Тип данных (string, int, bool, json и т.д.)
        @param ?string $description Описание ключа (для админки/документации)
        @param string  $environment Окружение (production/dev/stage)
        @param ?int    $updatedBy   Кто добавил (id пользователя) Автор изменений: в реальном маркетплейсе важно знать, кто поменял настройку.
        @param ?string $lang        Язык (только для группы 2, default = en)
    */
     public function addSetting(string $key,mixed $value,int $group,string $type = 'string',?string $description = null,string $environment = 'production',?int $updatedBy = null,?string $lang= null): void {

        // Проверка допустимых групп
        $allowedGroups = [1, 2, 3]; // (1=config, 2=lang, 3=db,...)
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
                1 => $this->saveToConfig($registryId, $value),
                2 => $this->saveToLang($registryId, $key, $value, $lang),
                3 => $this->saveToDb($registryId, $value),
                default => throw new \Exception("Unknown group {$group}"),
            };
        });
    }

    /**
        Сохранение в config/settings.php (группа 1).

        Установить конфиг даные в .\config\settings.php

        $registryId - id ключа в Таблице settings_registry
        $value      - Значения ключа
     */
    protected function saveToConfig(int $registryId, mixed $value): void
    {
        // !Добавить кеш (он должен быть отдельно для каждой группы чтобы при каждом изменении дергать ту группу в которой и были изменения а не все 3)

        // Получить путь
        $path = config_path('settings.php');
        $dir = dirname($path);

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

        // Сохраняем под ключом registryId
        // $registryId - подкаким id в таблице settings_registry граниться ключ итого конфига
        // Пример как будет запись:
        //  .\config\settings.php
        //  return [
        //          1 => json
		//  ];
        $settings[$registryId] = $value;

        // Формируем содержимое файла PHP
        $export = var_export($settings, true);
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

        Log::info("Setting saved to config", ['registry_id' => $registryId, 'path' => $path]);
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
        string $lang = 'en'            // Язык перевода или default
    ): void {

        // !Добавить кеш (он должен быть отдельно для каждой группы чтобы при каждом изменении дергать ту группу в которой и были изменения а не все 3)

        try {
            // Список поддерживаемых локалей
            $locales = config('app.supported_locales', ['en', 'ru']);
            if (!in_array($lang, $locales, true)) {
                Log::error("Unsupported locale '{$lang}' for setting '{$key}'");
                throw new \Exception("Locale '{$lang}' is not supported");
            }

            // Путь до нужного файла (general.php - этот файл строго для этого сервиса)
            $path = lang_path("{$lang}/settings/general.php");

            // Подгружаем текущие значения
            $settings = file_exists($path) ? include $path : [];

            // обновляем или добавляем ново
            // Пример что должно получиться:
            // return [
            //	 'name_site' => 'Морковь',
            // ];
            $settings[$key] = $value;

            // сохраняем файл обратно
            $content = "<?php\n\nreturn " . var_export($settings, true) . ";\n";
            file_put_contents($path, $content);

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

    // -- End -- конез методов для создания конфиг данных --------------------------------------------------------------



























}
