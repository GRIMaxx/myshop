<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;


Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');		// 	Главная страница

});

// Маршруты поисковой симстемы
require __DIR__ . '/search.php';

// -- Для тестов контроллер!
Route::controller(TestController::class)->group(function () {
    Route::get('/test', 'index')->name('test');		// 	Главная страница
});
