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
        // g-1
        //$this->setting->addSetting('site_name', 'MyShop', 1);

        // g-2 ru
        //$this->setting->addSetting('autor', 'Роман', 2, 'string','ru');

        // g-3
        //$this->setting->addSetting('support_email', 'support@myshop.com', 3);

        // полуить
        //dd($this->setting->get('autor','Тест','ru'));

        // обновить
        //$this->setting->updateSetting('autor', 'GX-1234');

        // удалить
        $this->setting->deleteSettingCache('site_name','ru');




            //string $type,
            //string $key,
            //mixed $value,
            //int|Carbon|null $ttl = null,
            //?string $service = null,
            //?string $lang = null,





        return view('welcome');
	}
}
