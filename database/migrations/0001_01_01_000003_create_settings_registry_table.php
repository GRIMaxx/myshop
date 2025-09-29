<?php
/**
    Мастер таблица - Справочник ключей
    ключи и описания живут только в этой таблице

 *  Можно для одного ключа хранить разные значения (по окружению, по магазинам и т.д.) без копирования описаний.
    Эта таблица  документация и гарантия уникальности ключей.

    Что такое группы:
    1.  .\config\settings.php      - тех. параметры и ссылки на ключи. (Здесь строго храним Статические которые очень редко меняються)
    2   .\lang\{locale}\settings\  - тексты, которые напрямую связаны с конфигурацией сайта.
    3.   БД                        - Динамические или часто обновляемые данные
 **/
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings_registry', function (Blueprint $table) {
			$table->id();

			// Уникальное имя ключа (site.name, seo.meta_title, checkout.min_order)
			$table->string('key', 191)->unique();

			// Номер группы (1 = config, 2 = lang, 3 = db)
			$table->tinyInteger('group')->comment('1=config, 2=lang, 3=db')->index();

			// Тип данных (string, int, bool, json и т.д.)
			$table->string('type', 50)->default('string');

            // Локаль - на каком языке храняться данные (только для группы 2)
            $table->char('locale', 5)->default('en')->index();

			// Описание ключа (чисто документация/админка)
			$table->text('description')->nullable();

			// Разделение по окружениям (dev/stage/prod)
			$table->string('environment', 50)->default('production')->index();

			// Флаги состояния: иногда удобно хранить явно
			$table->boolean('is_active')->default(true)->index();

			// Опционально — locked: чтобы защитить часть настроек от случайного изменения в админке.
			$table->boolean('is_locked')->default(false);

			// Автор изменений: в реальном маркетплейсе важно знать, кто поменял настройку.
			$table->unsignedBigInteger('updated_by')->nullable();

			$table->timestamps();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_registry');
    }
};
