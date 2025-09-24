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
         // Тестирую 2 групу создать или добавить но новую запись

        //$this->setting->addSetting('site_name', 'MyShop', 1);




        return view('welcome');
	}
}
