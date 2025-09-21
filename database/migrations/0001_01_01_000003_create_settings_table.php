<?php
/**
	Основные настройки сайта (03.08.2025)




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
		Schema::create('settings', function (Blueprint $table) {
			$table->id();

            // Основные
            $table->string('key', 128)->unique();                   // Уникальный ключ
            $table->json('value');                                        // Значение (JSON)

            // Контекст
            $table->string('environment', 50)                       // Разделение по окружениям (dev/stage/prod)
                ->default('production')
                ->index();

            $table->string('group', 32)                             // Быстрый поиск по группам -> (core/seo/payment/etc)
                ->default('general')
                ->index();

            // Метаданные
            $table->string('schema_version', 10)                    // Версия данных
                ->default('1.0')
                ->index();

            $table->timestamp('expires_at')->nullable()                   // Для временных настроек
                ->index();

            // Дополнительно
            $table->boolean('is_active')->default(true)->index();   // Флаги состояния: иногда удобно хранить явно:
            $table->boolean('is_locked')->default(false);           // Опционально — locked: чтобы защитить часть настроек от случайного изменения в админке.
            $table->string('description', 255)->nullable();        // Комментарий/описание: админам будет проще ориентироваться.
            $table->unsignedBigInteger('updated_by')->nullable()->index();// Автор изменений: в реальном маркетплейсе важно знать, кто поменял настройку.

            // Индексы
            $table->index(['group', 'environment']);
            $table->index(['key']);

            $table->timestamps();
		});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
