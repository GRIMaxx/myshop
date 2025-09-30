<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    //  Возвращаем макет главной страницы сайта
    public function index(): View
    {
        $store = 'default';
        $title = 'Главная';
        $description = 'Добро пожаловать';

        return view("stores.$store.home", compact('store', 'title', 'description'));
    }
}
