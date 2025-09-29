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
        ?string $lang = null,
        ?string $description = null,
        string $environment = 'production',
        ?int $updatedBy = null
    ): void;

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
    ): bool;





}
