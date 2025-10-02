// Плагин PWA система
// Регистрация pwa: .\resources\js\customs\index.ts

import { DEFAULT_CONFIG_PWA, getValidPWAConfigServer } from './config/default_config_pwa.js'; 
import { detectPWAOff } from './utils/helpers_pwa.js'     
import { getDataFromPage } from './utils/data_pwa.js';
import { register } from './utils/registerServiceWorker.js';
import { Prompt  } from './utils/prompt.js';

const initPWA = () => {

    const px = '[PWA:INIT]';

	// Проверка поддержки Service Worker
	if (!('serviceWorker' in navigator)) {
		console.error(`${px} Браузер не поддерживает ServiceWorker`);
		return;
	}

	// Если PWA выключен, прекращаем выполнение
	if (detectPWAOff(DEFAULT_CONFIG_PWA?.attribute)) return;
	
	// Получаем данные из DOM полученые от сервера
	let pwaData = getDataFromPage(DEFAULT_CONFIG_PWA?.idData);
	
	// Данные не удалось получить
	if (!pwaData) {
        // Используем по умолчанию (Автономный режим)
		pwaData = DEFAULT_CONFIG_PWA;
		console.error(`${px} Не удалось загрузить данные для PWA (Автономный режим включен.)`);
        return;
    }

	// Получить окончательные даные проверенные
	const CONFIG = getValidPWAConfigServer(pwaData);
	
	// -- PWA (Progressive Web App) --------------------------------------------------------------

	// 1. Регистрирует Service Worker с проверками поддержки, безопасностью и механизмом обновлений
	//    Файл CONFIG.serviceWorkerFile в PWA (Progressive Web App) играет ключевую роль в работе 
	//    оффлайн-функций (offline.html), кэширования и фоновых процессов.
	//    Функция может быть отключена без ущерба другому коду.
	if(CONFIG.serviceWorker !== false){
		register(CONFIG);
    }
	
	// 2. Установка на устройство, сайт как приложение (иконка на рабочем столе, запуск в отдельном окне)
	//    Splash screen
	if(CONFIG.pwaInstall !== false){
		// Старт PWA-функция установки
		Prompt(CONFIG);
    }
	
	//3. Push-уведомления (Web Push)
    // Пока в тиории!
};
document.addEventListener('DOMContentLoaded', initPWA);	// Для автоматической инициализации при загрузке
//export default initPWA;								// Экспорт для ручного вызова если нужно