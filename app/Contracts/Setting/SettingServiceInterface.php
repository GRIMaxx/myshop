<?php
/***

   Правило - Интерфейс - должен описывать только публичные методы, которые реально нужны снаружи.
   Все дополнительные или вспомогательные методы здесь не устанавливать!
 ***/
namespace App\Contracts\Setting;

interface SettingServiceInterface
{
    /**
     * Добавить новую настройку.  (Основная)
     *
     * Примеры:
     *  - Группа 1 (config): сохранит в config/settings.php
     *  - Группа 2 (lang): сохранит в resources/lang/{locale}/settings/general.php
     *  - Группа 3 (db): сохранит в таблицу settings
     */
    public function addSetting(
        string $key,
        mixed $value,
        int $group,
        string $type = 'string',
        ?string $description = null,
        string $environment = 'production',
        ?int $updatedBy = null,
        ?string $lang = null
    ): void;

    /**
     * Сохранение в config/settings.php (группа 1).
     * Обычно вызывается только из addSetting().
     * public function saveToConfig(int $registryId, mixed $value): void;
     */

    /**
     * Сохранение в lang/{locale}/settings/general.php (группа 2).
     * Обычно вызывается только из addSetting().
     * public function saveToLang(
     *   int $registryId,
     *   string $key,
     *   mixed $value,
     *   string $lang = 'en'
     * ): void;
     */

    /**
     * Сохранение в таблицу settings (группа 3).
     * Обычно вызывается только из addSetting().
     * public function saveToDb(int $registryId, mixed $value): void;
     */

    /**
     * Нормализация значения для config.
     * - объекты → массивы
     * - ресурсы и closures запрещены
     * public function normalizeConfigValueForExport(mixed $value): mixed;
     */
    // END - Конец мнтодов обработки создания новой записи



}
