<?php
/**
	Данная таблица предназначена для хранения 3 группы данных
	3 Группа данных это динамические даные для админки или других часто обновляемых конфигов.
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
			
			// Ссылка на ключ в registry
			$table->foreignId('registry_id')->constrained('settings_registry')->cascadeOnDelete();
			
			// Значение (JSON для универсальности)
			$table->json('value');
			
			$table->timestamps();

			$table->index('created_at');
			$table->index('updated_at');
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
