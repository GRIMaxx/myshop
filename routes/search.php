<?php
// Поисковая система (Охранник web)
// Почему подключаю в \routes\web.php а не .\bootstrap\app.php дело в том что мне нужно получать маршруты в \app\Services\SearchService
// сервисе а это значит если их зарегистрировать здесь .\bootstrap\app.php они не успевают быть готовыми
// по этому для супер быстрости и с гарантией лучше так.
// если маршруты использовались только в blade.php то регистрируем здесь .\bootstrap\app.ph и все успевает
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SearchController;

Route::middleware('throttle:60,1')->group(function () {				// Контроль запросов в минуту 60 шт.
	Route::controller(SearchController::class)->group(function () {     // Контроллер поисковой системы

        // Основной маршрут поисковой системы
        Route::post('/search', 'search')->name('search');


	});
});
