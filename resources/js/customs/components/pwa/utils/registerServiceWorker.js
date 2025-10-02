/**
	Service Worker - Создан 29.05.2025-GX

    ---------------------------------------------------------------------------------
    Как я передаю даные в самую конечную точку механизма pwa: pwa.js -> sw.js -> offline.html
	--
	В этом файле 
		Собрать конфиг даные для SW и сохранить в БД
		Собрать конфиг данные для offline.html страницы и сохранить в БД 
	В файле sw.js
		Извлекаем даные из sw_data предназеаченые только для SW в переменую swConfig
	    Сохраняю в БД из swConfig даные которых не хватает для offline.html
	--
	Это найлучший вариант из всех нет дублей и максимально просто.
	---------------------------------------------------------------------------------
**/
import { log } from '../utils/logger.js';					// log(message,diagnostics=false,type='log',prefix='[PWA]') 
import { getMessageSize } from '../utils/helpers_pwa.js'    // Замер веса даных для отправки
import { IDB } from '../utils/idb.js';						// Подключаем БД

export const register = (confSR = {}) => {
	const px = '[PWA:SW:REGISTER]';

    // Проверка пустого объекта
	if (!confSR || Object.keys(confSR).length === 0){
		log(`Нет двнных.`, 'error', px);
		return;
	}
	
	// --- Проверка протокола ---
	if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
		log(`Service Worker requires HTTPS.`, 'error', px);
		return; 													// Блокировка регистрации на HTTP в продакшене
	}

	// --- Защита от повторной регистрации через Symbol ---
	const SW_REGISTERED_KEY = Symbol.for('MY_APP_SW_REGISTERED');
	if (window[SW_REGISTERED_KEY]) return; 							// Если уже зарегистрирован - пропускаем
	window[SW_REGISTERED_KEY] = true; 								// Помечаем как зарегистрированный

    // Основная функция регистрации и управления Service Worker
	const registrationHandler = async () => {
		
		// Конфигурация с дефолтными значениями
		const config = {
			updateInterval: confSR.configSW.swUpdateInterval || 86400000,// 24 часа
			initialDelay: 	confSR.configSW.swInitialDelay || 10000, 	// 10 сек до первого обновления
			retryDelay: 	confSR.configSW.swRetryDelay || 60000, 		// 60 сек между повторами
			maxRetries: 	confSR.configSW.swMaxRetries || 5,			// Макс. попыток обновления
			appVersion: 	confSR.configSW.appVersion || '1.0.0', 		// Версия для кэширования
			maxRetryDelay: 	confSR.configSW.maxRetryDelay || 300000, 	// 5 мин макс. задержка
			minRetryDelay: 	confSR.configSW.minRetryDelay || 5000, 		// 5 сек мин. задержка
			cleanupOnUnload:confSR.configSW.cleanupOnUnload !== false 	// Флаг очистки
		};

		// Переменные состояния
		let updateInterval = null; 									// Таймер проверки обновлений
		let isUpdating = false; 									// Флаг процесса обновления
		let registration; 											// Объект регистрации SW
		let retryCount = 0; 										// Счётчик неудачных попыток

		//-- Собрать даные для отправки (Дублей нет)
		
		// Собрать конфиг даные для sw.js 
		const swConfig = {
			appName: 	 confSR.app.name,      						// Название сайта
			appVersion:  confSR.app.version,   						// Версия сайта
			appLang:     confSR.app.lang,				 			// Язык сайта 
			appTheme:    confSR.app.theme,				 			// Тема сайта (темная светлая)
			diagnostics: confSR.diagnostics, 						// Режим отладки механизма PWA + SW
			cacheName:   confSR.app.cacheName,						// Имя кеша
            cacheVersion:confSR.app.cacheVersion,					// Версия кеша
			lastUpdate:  new Date().toISOString()                   // 
		};

		// Собрать конфиг данные для offline.html страницы
		const swConfigOffline = {
			trans:      confSR.trans,								// Переводы текстовые 
			codOffline: confSR.codOffline                           // Дополни
		}

		// Функция установки и отправки конфиг данных в SW
		const updateConfig = async () => {
			
			// Максимально --> 100 КБ (100 × 1024 байт) для передачи данных в Service Worker
			getMessageSize(swConfig, {limit:100 * 1024, log: true});// Проверить на лимит данных 
			getMessageSize(swConfigOffline, {limit:100 * 1024, log: true});// Проверить на лимит данных
		
			// Сохраняем в БД Настройки для SW (1 – 10 МБ)
			await IDB.saveData(
				'sw_config', 										// Флаг какие данные применить включая БД
				'sw_data',											// Под каким ключем в БД будет храниться данные  
				swConfig											//
			);

            // Сохраняем в БД Настройки для offline.html (1 – 10 МБ) 
			await IDB.saveData(
				'offline', 											// Флаг какие данные применить включая БД
				'offline_data',										// Под каким ключем в БД будет храниться данные  
				swConfigOffline										//
			);

			const messageChannel = new MessageChannel(); 			// Создаём канал для ответа от SW
			
			// Дпополнительная отправка для надежности (100 КБ – 1 МБ)			
			if (navigator.serviceWorker.controller) {
				navigator.serviceWorker.controller.postMessage(
					{
						type: 'CONFIG_UPDATE',
						payload: {
							sw: swConfig,       					// Конфиг для Service Worker
							offline: swConfigOffline  				// Конфиг для offline.html
						}
					},
					[messageChannel.port2] 							// Передаём порт
				);
				
				// Ждём ответа через порт
				// Если ответил значит даные получены и сохранены все ок.
		        // ...
				messageChannel.port1.onmessage = (event) => {
					if(event.data.status == 'CONFIG_UPDATED'){
						log("SW (Ответ) -> данные получил и сохранил.", 'info', '[PWA:SW:MSG]');        // 'CONFIG_UPDATED'	
					}
				};
			}
			
			
			// Это механизм синхронизации конфигурации между всеми вкладками приложения через BroadcastChannel.
			// BroadcastChannel — это API, позволяющий разным вкладкам/окнам браузера обмениваться сообщениями в реальном времени.
			// Канал 'sw-config' — это виртуальный "чат", к которому могут подключиться все экземпляры приложения.
			// postMessage отправляет данные (например, новый конфиг) всем подписчикам канала.
            // Зачем это нужно? 
			// Пользователь открыл 5 вкладок приложения. Обновил настройки в одной — остальные не знают об изменениях.
            // Ивот теперь этот код передаст --> все вкладки мгновенно получают новый конфиг и синхронизируются.
			const bc = new BroadcastChannel('sw-config');
			bc.postMessage({ type: 'CONFIG_UPDATE', payload: {sw: swConfig, offline: swConfigOffline} });
		};

		// Обновление Service Worker с обработкой ошибок
		const safeUpdate = async () => {
			
			// Предотвращение параллельных обновлений
			if (isUpdating) {
				log(`Update already in progress`, 'info', px);
				return;
			}
		
			try {
				isUpdating = true;
				const reg = registration || await navigator.serviceWorker.ready;
				
				// Это не ошибка, но стоит уточнять статус SW (installing, waiting, active).
				// Когда это происходит и почему это нормально?
				// 1. При первой загрузке страницы
				//    Service Worker (SW) регистрируется, но ещё не активирован (installing → waiting → activated).
				//    Пока SW не активирован, navigator.serviceWorker.controller будет null.
				//    Решение: Ждём активации (обычно занимает несколько секунд).
				// 2. После обновления SW
				//    Новый SW установлен, но ждёт, пока старый не освободит клиенты (страницы).
				//    Пока старая версия SW контролирует страницу, новая версия не станет controller.
				//    Решение: Обновите страницу или вызовите skipWaiting() в SW.
				// 3. Если SW не контролирует страницу
				//    Например, пользователь открыл сайт в новой вкладке, а SW ещё не взял управление.
				//    Решение: Проверить scope SW и перезагрузить страницу.
				if (!reg.controller) {
					if (reg.installing) {
						log(`SW installing (not yet active)`, 'info', px); 					// Синий - информация
					} else if (reg.waiting) {
						log(`SW waiting for activation (reload page to update)`, 'warn', px); // Жёлтый - требует действия
					} else {
						log(`No active SW controller (may be first load)`, 'warn', px); 		// Жёлтый - не критично
					}
					return;
				}

                log(`Checking for updates...`, 'info', px); 
				await reg.update(); 							// Принудительное обновление
				
				log(`Update check completed - ${reg.installing ? 'new version found' : 'already up-to-date', null, px}`, 'success');
				
				retryCount = 0; 								// Сброс счётчика при успехе
			} catch (err) {
				retryCount++;
				const nextRetryDelay = Math.min(				// Расчет экспоненциальной задержки с ограничениями
					Math.max(config.retryDelay * retryCount, config.minRetryDelay),
					config.maxRetryDelay
				);

				// Разделил сообщение и ошибку для лучшей читаемости
				log(`Update attempt failed (${retryCount}/${config.maxRetries})`, 'warn', px);
				log(`Error details: ${err.message || err}`, 'debug', px); // Добавил уровень 'debug'
                // Повторная попытка, если не превышен лимит
				if (retryCount < config.maxRetries) {
					log(`Retrying in ${Math.round(nextRetryDelay/1000)}s...`, 'info', px);
					setTimeout(safeUpdate, nextRetryDelay);
				} else {
					log(`Maximum update attempts reached - automatic updates paused`, 'warn', px); // Не red
					stopUpdateCheck();                      // Остановка при превышении попыток
				}
			} finally {
				isUpdating = false;
			}
		};
		
		// Запускает периодическую проверку обновлений
		const startUpdateCheck = () => {
			if (!updateInterval) {
				// Установка интервальной проверки
				updateInterval = setInterval(safeUpdate, config.updateInterval);
				
				log(`Update checks started`, null, px);
				
				// Первая проверка после задержки
				setTimeout(safeUpdate, config.initialDelay);
			}
		};

		// Останавливает проверку обновлений
		const stopUpdateCheck = () => {
			if (updateInterval) {
				clearInterval(updateInterval);
				updateInterval = null;
				log(`Update checks stopped`, null, px);
			}
		};
	
		try {
	
			// Очистка старых Service Worker'ов в режиме разработки --> development
			if (process.env.NODE_ENV === 'development') {
				try {
					const regs = await navigator.serviceWorker.getRegistrations();
					await Promise.all(
						regs.map(reg => 
							reg.unregister()
								// Эли нет старых данных для очистки - Это не ошибка!
								.then(() => log(`Unregistered old SW`, null, px))
								.catch(err => log(`Unregister warning:` + err, 'error', px))
						)
					);
				} catch (unregisterError) {
					log(`Safe unregister failed:` + unregisterError, 'error', px);
				}
			}

			// Сохраняем конфиг перед регистрацией
			await updateConfig();

            // -- РЕГИСТРАЦИЯ --

			// Если браузер поддерживает модули
			if (window.navigator.userAgent.includes('Chrome') || 'noModule' in HTMLScriptElement.prototype) {
				const swUrl = `${confSR.serviceWorkerFile}?v=${confSR.app.version}`;
				registration = await navigator.serviceWorker.register(
					swUrl, { 
						scope: '/',
						// Перейти на модульный Service Worker (ES Modules).
						// Поддержка { type: 'module' } есть в современных браузерах 
						// (Chrome 91+, Firefox 90+, Edge 91+, Safari 15+), но нет в IE и 
						// старых Safari.
						type: 'module'						
					}
				);
			} else {
				// Если нужна совместимость со старыми браузерами
				// fallback для старых браузеров
				const swUrl = `${confSR.serviceWorkerFile1}?v=${confSR.app.version}`;
				registration = await navigator.serviceWorker.register(swUrl, {scope: '/'});
			}
			
			// Обработчик активации
			const handleWorkerActivation = (worker) => {
				if (worker.state === 'activated') {
					// При активации дублируем отправку конфига
					// Отправляем конфиг только если это новая активация
					if (!worker.isUpdate) {
						// Дублируем отправку конфига при первой активации
						updateConfig();
					}
				} else {
					const stateChangeHandler = () => {
						if (worker.state === 'activated') {
							worker.removeEventListener('statechange', stateChangeHandler);
							// Дополнительные действия после активации
							if (!worker.isUpdate) {
								updateConfig();
							}
						}
					};
					worker.addEventListener('statechange', stateChangeHandler);
				}
			};
			
			// Отслеживание обновлений
			registration.addEventListener('updatefound', () => {
				const newWorker = registration.installing;
				newWorker.isUpdate = true;							// Помечаем как обновление
				newWorker.addEventListener('statechange', () => {
					if (newWorker.state === 'installed') {
						if (navigator.serviceWorker.controller) {
							log(`New SW version installed`, null, px);
							// Можно показать UI-уведомление о доступности обновления
						}
						handleWorkerActivation(newWorker);
					}
				});
			});

			// Управление проверками
			const handleVisibilityChange = () => {
				document.visibilityState === 'visible' 
					? startUpdateCheck() 						// Возобновляем проверки когда вкладка активна
					: stopUpdateCheck();						// Приостанавливаем проверки когда вкладка невидима
			};

            // Очистка ресурсов при закрытии страницы
			const cleanup = () => {
				if (config.cleanupOnUnload) {
					stopUpdateCheck();
					document.removeEventListener('visibilitychange', handleVisibilityChange);
				}
			};
				
			// Старт основных процессов
			startUpdateCheck(); 													// Запускаем периодические проверки
				
			document.addEventListener('visibilitychange', handleVisibilityChange); 	// Подписываемся на изменения видимости
			window.addEventListener('pagehide', cleanup, { once: true }); 			// Очистка при закрытии страницы
			window.addEventListener('beforeunload', cleanup, { once: true }); 		// Дублирующая очистка

			// Глобальный метод для обновления конфига
			window.updateServiceWorkerConfig = async (key, value) => {
				if (!swConfig) {
					log(`Config not initialized`, 'error', px);
					return false;
				} 
				try {
					swConfig[key] = value;
					await updateConfig();
					return true;
				} catch (err) {
					log(`Config update failed: ${err}`, 'error', px);
					return false;
				}
			};

		} catch (err) {
				
			// Обработка ошибок регистрации с повторными попытками
			log(`Registration failed (${retryCount + 1}/${config.maxRetries})` + err, 'error', px);
				
			if (retryCount < config.maxRetries) {
				const delay = Math.min(
					config.retryDelay * (retryCount + 1),
					config.maxRetryDelay
				);
					
				await new Promise(resolve => setTimeout(resolve, delay));
				retryCount++;
				return registrationHandler(); 					// Рекурсивный повтор
			}
				
			// Финальная обработка при исчерпании попыток
			window[SW_REGISTERED_KEY] = false; 					// Разблокировка для будущих попыток
			log(`Max registration attempts reached`, 'error', px);
		}
	};

	// Функция `safeInit` обеспечивает безопасную инициализацию сервис-воркера
	// с обработкой ошибок и учетом состояния загрузки страницы.
	const safeInit = () => {
		try {
			// Проверяем, полностью ли загружена страница (включая все ресурсы)
			if (document.readyState === 'complete') {
				// Если страница уже загружена, сразу запускаем регистрацию сервис-воркера
				registrationHandler().catch(err => 
				log(`Registration handler error:` + err, 'error', px)); // Логируем ошибки
			} else {
				// Если страница ещё загружается, создаём обработчик `init`
				const init = () => {
					// Удаляем оба обработчика событий, чтобы избежать дублирования вызовов
					window.removeEventListener('DOMContentLoaded', init);
					window.removeEventListener('load', init);
						
					// Запускаем регистрацию сервис-воркера
					registrationHandler().catch(err => 
						log(`Registration handler error:` + err, 'error', px)); // Логируем ошибки
				};
					
				// Подписываемся на событие DOMContentLoaded (DOM готов, но ресурсы могут ещё грузиться)
				// { once: true } гарантирует, что обработчик сработает только один раз
				window.addEventListener('DOMContentLoaded', init, { once: true });
					
				// Подписываемся на событие load (страница и все ресурсы полностью загружены)
				window.addEventListener('load', init, { once: true });
			}
		} catch (initError) {
			// Ловим и логируем любые ошибки в самом процессе инициализации
			log(`Initialization error:` + initError, 'error', px);
		} 
	};

	// Вызываем функцию инициализации
	safeInit();
};