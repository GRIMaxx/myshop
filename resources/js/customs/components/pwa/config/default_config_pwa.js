// Конфиги для PWA

import {isValidString,isValidNumber,isValidNonEmptyObject,isValidBooleanExtended,getPreferredTheme} from '../utils/helpers_pwa.js'

// Имена БД 
const NAME_PWA = 'PWA_ConfigDB';
const NAME_SW = 'SW_ConfigDB';
const NAME_OFF = 'OFF_ConfigDB';

// Версии БД
const dbVersions = {
	[NAME_SW]: 1,										// Версия SW						
	[NAME_PWA]: 1,										// Версия WPA
	[NAME_OFF]: 1,										// Версия
};

// Константа база для дефолтных значений
export const DEFAULT_CONFIG_PWA = {
 	
	idData:    			'r-r-n-1',                   	// ID дива который хранит данные от сервера
	attribute: 			'data-pwa',						// Названия атрибута (вкл. выкл. PWA <html ....) 			
	
	app:{
		name: 			'Gx',							// Названия сайта
		version:    	'1.0.0',               			// Версия сайта
		theme:      	'light',						// Тема сайта
		lang:			'en',							// Язык сайта
	    cacheName:      'GX-1.0.0',                     // Базовое имя кэша (для кеширования всех файлов для ofline)
		cacheVersion:   'v1',                           // Базовая версия кеша (для кеширования всех файлов для ofline)    
	},
	
	db: {
		[NAME_PWA]: {
			version: 	dbVersions[NAME_PWA]			// Версия БД
		},
		[NAME_SW]: {
			version: 	dbVersions[NAME_SW]
		},
	},
	
	logsOn:             true,                           // Показывать логи в PWA да нет
	diagnostics: 		false,							// Включить вывод логов
	remindAfterHours: 	24,								// Часы до повторного показа prompt
	
	serviceWorkerFile: 	'/sw.js',						// Основной sw который подержует import
	serviceWorkerFile1: '/swl.js',						// Дополнительный если старые браузеры не подержуют import
	
	promptDelay: 		3500,
	serviceWorker:		false,
	pwaInstall:         false,
	modalClass:         '',
	
	// Конфигурация с дефолтными значениями
	// Для регистрации SW
	configSW: {
		updateInterval: 86400000, 						// 24 часа
		initialDelay:   10000, 							// 10 сек до первого обновления
		retryDelay:     60000, 							// 60 сек между повторами
		maxRetries:     5, 								// Макс. попыток обновления
		maxRetryDelay:  300000, 						// 5 мин макс. задержка
		minRetryDelay:  5000, 							// 5 сек мин. задержка
		cleanupOnUnload: true,							// Флаг очистки
	},

    // Текст
	trans: {
		offline: 		"Offline",
		description: 	"",
		keywords: 		"",
		author: 		"GX",
		h1: 			"Oops! Looks like you're offline!",
		p: 				"Please check your internet connection, or try refreshing the page.",
		button: 		"Try again",
		footer: 		"© All rights reserved. Made by",
	},

	// Текст дополнительно
	codOffline: 		{},	    						//
	
	ui: {
		installTitle: 	"Установите Gx",
		installTitle1: 	"Установите Gx app",
		installText: {
			default: 	"Add Gx to your home screen for quick and easy access to your shopping anytime, anywhere!",
			safari: 	"Add Gx to your home screen for quick and easy access to your shopping anytime, anywhere! Tap the <span class='fw-semibold'>'Share'</span> icon in Safari and select <span class='fw-semibold'>'Add to Home Screen'</span> from the options.",
			android: 	"Add Gx to your home screen for quick and easy access to your shopping anytime, anywhere!",
		},
	},
	
	buttons: {
		install: {
			text: 		"Установить",
			icon: 		"ci-download fs-base me-1 ms-n1",
		},
		later: {
			text: 		"Напомнить позже",
			icon: 		"ci-clock fs-base me-1 ms-n2",
		},
		dismiss: {
			text: 		"Не предлагать",
			icon: 		"ci-close fs-base me-1 ms-n2",
		},
	},
	allowedStorages: {									// Хранидлище даных в Браузере (dBName.имя)		
		sw_config: {									// Флаг - по нему определяем какие даные подключить для БД 							
			dbName: 	NAME_PWA,						// Названия основной БД для PWA 
			storeName: 	'config',						// Хранилище с даными их туда передает PWA (названия как аналог таблицы в phpmyadmin)
			storeVersion: 1,         					// Названия хранилища
			maxAge: 	86400 * 3,						// Время жизни хранилища
			indexes: [   								// Описание индексов
				{
					name: 'timestamp_idx',
					keyPath: 'timestamp',
					options: { unique: false }
				}
			]
		},
		offline: {										// Для страницы offline.html данные
			dbName: 	NAME_SW,					 
			storeName: 	'offlineData',
			storeVersion: 1,
			maxAge: 	86400 * 3,
			indexes: [
				{
					name: 'timestamp_idx',
					keyPath: 'timestamp',
					options: { unique: false }
				}
			]
		},
		cache_grouped: {								// Для страницы для кеширования всех файлов всей системы PWA
			dbName: 	NAME_OFF,
			storeName: 	'cacheGroupedData',
			storeVersion: 1,
			maxAge: 	86400 * 3,
			indexes: [
				{
					name: 'timestamp_idx',
					keyPath: 'timestamp',
					options: { unique: false }
				}
			]
		}	
	}, 

};

// функция-валидатор полученых даных от сервера и дефолта
export const getValidPWAConfigServer = (pwaData) => {
	if (!isValidNonEmptyObject(pwaData)) return DEFAULT_CONFIG_PWA;
	if(!pwaData) return DEFAULT_CONFIG_PWA;
     
	const getValidTheme = () => {
		const themeFromData = pwaData?.app?.theme || 'light';
		if (isValidString(themeFromData)) {
			return themeFromData;
		}
		const preferredTheme = getPreferredTheme();
		if (isValidString(preferredTheme)) {
			return preferredTheme;
		}
		return DEFAULT_CONFIG_PWA.app.theme;
	};

    // Включить логирования
    self.diagnostics = isValidBooleanExtended(pwaData?.diagnostics, DEFAULT_CONFIG_PWA.diagnostics);

	const appName    = isValidString(pwaData?.app?.name, 	DEFAULT_CONFIG_PWA.app.name);
	const appVersion = isValidString(pwaData?.app?.version, DEFAULT_CONFIG_PWA.app.version);
	const appLang    = isValidString(pwaData?.app?.lang, 	DEFAULT_CONFIG_PWA.app.lang);
	
	// Имя кеша - Сэтим именем будем хранить все кешированые файлы для offline
	const cachename = appName + "_" + appVersion + "_" + appLang || DEFAULT_CONFIG_PWA.app.cacheName;

	return {
		app: isValidNonEmptyObject(pwaData?.app) ? {		
			name: 			appName, 
		    version:		appVersion,		
			theme: 			getValidTheme(),
			lang:			appLang,			
		    cacheName:      cachename,
            cacheVersion:   DEFAULT_CONFIG_PWA.app.cacheVersion,			
		} : DEFAULT_CONFIG_PWA.app,	
		
		diagnostics: 		self.diagnostics,
		configSW: 			DEFAULT_CONFIG_PWA.configSW,
		serviceWorkerFile: 	isValidString(pwaData?.serviceWorkerFile,      DEFAULT_CONFIG_PWA.serviceWorkerFile),
		serviceWorkerFile1: isValidString(pwaData?.serviceWorkerFile1,     DEFAULT_CONFIG_PWA.serviceWorkerFile1),
		allowedStorages:    DEFAULT_CONFIG_PWA.allowedStorages,
		db:					DEFAULT_CONFIG_PWA.db,
		remindAfterHours: 	isValidNumber(pwaData?.remindAfterHours,       DEFAULT_CONFIG_PWA.remindAfterHours),
		promptDelay: 		isValidNumber(pwaData?.promptDelay,            DEFAULT_CONFIG_PWA.promptDelay),
		serviceWorker:		isValidBooleanExtended(pwaData?.serviceWorker, DEFAULT_CONFIG_PWA.serviceWorker), 
		pwaInstall:			isValidBooleanExtended(pwaData?.pwaInstall,    DEFAULT_CONFIG_PWA.pwaInstall),
		codOffline: 		isValidNonEmptyObject(pwaData?.codOffline,     DEFAULT_CONFIG_PWA.codOffline),
        modalClass: 	    isValidString(pwaData?.modalClass,             DEFAULT_CONFIG_PWA.modalClass),   

		// Переводы для страницы offline.html
		trans: isValidNonEmptyObject(pwaData?.trans) ? {
			offline: 		isValidString(pwaData?.trans?.offline,     DEFAULT_CONFIG_PWA.trans.offline),
			description: 	isValidString(pwaData?.trans?.description, DEFAULT_CONFIG_PWA.trans.description),
			keywords: 		isValidString(pwaData?.trans?.keywords,    DEFAULT_CONFIG_PWA.trans.keywords),
			author: 		isValidString(pwaData?.trans?.author,      DEFAULT_CONFIG_PWA.trans.author),
			h1: 			isValidString(pwaData?.trans?.h1,          DEFAULT_CONFIG_PWA.trans.h1),
			p: 				isValidString(pwaData?.trans?.p,           DEFAULT_CONFIG_PWA.trans.p),
			button: 		isValidString(pwaData?.trans?.button,      DEFAULT_CONFIG_PWA.trans.button),
			footer: 		isValidString(pwaData?.trans?.footer,      DEFAULT_CONFIG_PWA.trans.footer),
		} : DEFAULT_CONFIG_PWA.trans,	
		
		ui: isValidNonEmptyObject(pwaData?.ui) ? {
			installTitle: 	isValidString(pwaData?.ui?.installTitle, 		DEFAULT_CONFIG_PWA.ui.installTitle),
			installTitle1: 	isValidString(pwaData?.ui?.installTitle1, 		DEFAULT_CONFIG_PWA.ui.installTitle1),
			installText:    isValidNonEmptyObject(pwaData?.ui?.installText, {
				default:    isValidString(pwaData?.ui?.installText?.default, DEFAULT_CONFIG_PWA.ui.installText.default),
				safari:     isValidString(pwaData?.ui?.installText?.safari,  DEFAULT_CONFIG_PWA.ui.installText.safari),
				android:    isValidString(pwaData?.ui?.installText?.android, DEFAULT_CONFIG_PWA.ui.installText.android),
			}),
		} : DEFAULT_CONFIG_PWA.ui,

		buttons: isValidNonEmptyObject(pwaData?.buttons) ? {
			install: isValidNonEmptyObject(pwaData?.buttons?.install, {
				text: isValidString(pwaData?.buttons?.install?.text, DEFAULT_CONFIG_PWA.buttons.install.text),
				icon: isValidString(pwaData?.buttons?.install?.icon, DEFAULT_CONFIG_PWA.buttons.install.text),
			}),
			later: isValidNonEmptyObject(pwaData?.buttons?.later, {
				text: isValidString(pwaData?.buttons?.later?.text, DEFAULT_CONFIG_PWA.buttons.later.text),
				icon: isValidString(pwaData?.buttons?.later?.icon, DEFAULT_CONFIG_PWA.buttons.later.icon),
			}),
			dismiss: isValidNonEmptyObject(pwaData?.buttons?.dismiss, {
				text: isValidString(pwaData?.buttons?.dismiss?.text, DEFAULT_CONFIG_PWA.buttons.dismiss.text),
				icon: isValidString(pwaData?.buttons?.dismiss?.icon, DEFAULT_CONFIG_PWA.buttons.dismiss.icon),
			}),
		} : DEFAULT_CONFIG_PWA.buttons,
	}; 
}; 