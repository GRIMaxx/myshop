<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Contracts\Setting\SettingServiceInterface;


class TestController extends Controller
{
    private SettingServiceInterface $setting;

    public function __construct(
        SettingServiceInterface $setting,
    ){
        $this->setting = $setting;
    }

    //  Возвращаем макет главной страницы сайта
    public function index(): View
    {
        // -- Установка конфигов ---------------------------------
        // g-1   storage
        //$this->setting->addSetting('search_show_md', true, 1);

        // Не удалять добавляю конфиги для поисковой системы!
        //$this->setting->addSetting('search', [
        //    'driver' => 'meilisearch',
        //
        //], 1, 'array');


        // g-2   переводы
        //$this->setting->addSetting('site_name_en', 'Carrot', 2, 'string','en');
        //$this->setting->addSetting('site_name_ru', 'Магазин,Продукты', 2, 'string','ru');

        // g-3
        //$this->setting->addSetting('support_email-2', 'support@myshop.com', 3);

        // полуить
        //dd($this->setting->get('autor','Тест','ru'));

        // обновить
        //$this->setting->updateSetting('autor', 'GX-1234');

        // удалить
        //dd($this->setting->deleteSettingCache('support_email-1',));



        //----------------------------------------------------------------
        $store = app('settings')->get('store');
        $title = 'Главная';
        $description = 'Добро пожаловать!';

        return view("stores.$store.home", compact('title','description'));
    }
}
