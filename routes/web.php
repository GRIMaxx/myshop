<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;



Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');								// 	Главная страница
});


//Route::get('/', function () {
//    return view('welcome');
//});

// Для теста 12345555333333
