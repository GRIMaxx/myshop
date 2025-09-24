<?php
namespace App\Http\Controllers;

use Illuminate\View\View;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Cache;
//use App\Services\FilterService;
//use App\Services\AttributeService;   			// для теста атрибутов
//use App\Services\CategoryService;    			// для теста
//use App\Services\Admin\AdminProductService;		// для теста

use App\Contracts\Setting\SettingServiceInterface;

class HomeController extends Controller
{
    private SettingServiceInterface $setting;

    public function __construct(SettingServiceInterface $setting)
    {
        $this->setting = $setting;
    }

    //public function __construct(
    //    private SettingServiceInterface $setting
    //) {}

	//  Возвращаем макет главной страницы сайта
    public function index(): View
    {
         // Тестирую 2 групу создать или добавить, но новую запись

        // гр -1
        //$this->setting->addSetting('currencies', ['USD', 'EUR', 'UAH'], 1, 'array');

        // гр - 2
        //$this->setting->addSetting('greeting', [
        //   'morning' => 'Доброе утро',
        //   'evening' => 'Добрый вечер',
        //], 2, 'array', lang: 'ru');

        // гр - 3
        //$this->setting->addSetting('payment_methods', [
       //    'paypal' => true,
        //   'stripe' => false,
        //], 3, 'json');

        // тестирую 1 группу
        //$this->setting->get('currencies');


        return view('welcome');
	}
}
