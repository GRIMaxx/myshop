import { log } from '../utils/logger.js';
// Только для PWA

// Валидация строки с дефолтным значением
// Доп. проверка мин символов
// Возвращает defaultValue для null/undefined/числа/объекта
export const isValidString = (value, defaultValue = '', minLength = 1) => 
    typeof value === "string" && value.trim().length >= minLength ? value : defaultValue;

// Валидация числа с дефолтным значением
// Доп. проверка диапазона
// Возвращает defaultValue для строк/NaN
export const isValidNumber = (value, defaultValue = 24, options = { min: -Infinity, max: Infinity }) => 
    typeof value === "number" && !isNaN(value) && value >= options.min && value <= options.max 
        ? value 
        : defaultValue;

// Валидация булевого значения с дефолтным значением
// Для булевых значений добавлена строковая интерпретация (опционально)
// Возвращает true/false или defaultValue
export const isValidBooleanExtended = (value, defaultValue = false) => {
    if (typeof value === "boolean") return value;
    if (typeof value === "string") {
        if (value.toLowerCase() === "true") return true;
        if (value.toLowerCase() === "false") return false;
    }
    return defaultValue;
}

// Валидация объекта с дефолтным значением
// Доп. проверка на пустоту 
// Возвращает defaultValue для примитивов
export const isValidNonEmptyObject = (value, defaultValue = {}) => 
    typeof value === "object" && value !== null && Object.keys(value).length > 0 
        ? value 
        : defaultValue;

// Проверяет, является ли значение пустым объектом {} или пустым массивом [].
export const isEmptyObjectOrArray = (value) => {
	if (!value || typeof value !== 'object') {
		return false;
	}
	return (
		(Object.keys(value).length === 0 && value.constructor === Object) ||
		(Array.isArray(value) && value.length === 0)
	);
};

// Проверка PWA режима
// Если <html ... data-pwa="true"> не установлен в true, отменяем регистрацию Service Worker'ов
// и прекращаем выполнение скрипта.
export function detectPWAOff(attribute = null) {
    if(attribute == null){return true;}
	const htmlElement = document.documentElement;
    if (htmlElement.getAttribute('data-pwa') !== 'true') {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations()
                .then(regs => regs.forEach(r => r.unregister()));
        }
        return true; 			// PWA выключен
    }
    return false; 				// PWA включен
}

// Обнаружение окружающей среды
export const Environment = {
    /**
     * Определение операционной системы
     * @returns {string} Название ОС (Android, iOS, Windows, macOS, Linux, ChromeOS и др.)
     */
    os: (() => {
        const ua = navigator.userAgent.toLowerCase();
        const platform = navigator.platform.toLowerCase();

        // Порядок важен! Сначала специфичные случаи (ChromeOS, WebOS), потом общие (Linux).
        if (/android/.test(ua)) return 'Android';
        if (/iphone|ipad|ipod/.test(ua)) return 'iOS';
        if (/windows/.test(ua) || /win/.test(platform)) return 'Windows';
        if (/macintosh|mac os x/.test(ua) || /mac/.test(platform)) return 'macOS';
        if (/cros/.test(ua)) return 'ChromeOS';
        if (/webos|web0s/.test(ua)) return 'WebOS';
        if (/tizen/.test(ua)) return 'Tizen';
        if (/playstation/.test(ua)) return 'PlayStation';
        if (/xbox/.test(ua)) return 'Xbox';
        if (/nintendo/.test(ua)) return 'Nintendo';
        if (/linux/.test(platform) && !/android/.test(ua)) return 'Linux';

        return 'Unknown';
    })(),

    /**
     * Определение браузера
     * @returns {string} Название браузера (Chrome, Firefox, Safari, Edge, Opera, Samsung и др.)
     */
    browser: (() => {
        const ua = navigator.userAgent.toLowerCase();

        if (ua.includes('samsungbrowser/')) return 'Samsung Internet';
        if (ua.includes('edg/') || ua.includes('edge/')) return 'Edge';
        if (ua.includes('opr/') || ua.includes('opera')) return 'Opera';
        if (ua.includes('firefox') || ua.includes('fxios')) return 'Firefox';
        if (ua.includes('chrome') && ua.includes('safari')) return 'Chrome';
        if (ua.includes('safari/') && !ua.includes('chrome')) return 'Safari';
        return 'Other';
    })(),

    /**
     * Проверка, является ли устройство мобильным (смартфон/планшет)
     * @returns {boolean} true, если мобильное устройство
     */
    isMobile: /mobi|android|iphone|ipad|ipod|windows phone/i.test(navigator.userAgent),

    /**
     * Проверка, запущено ли приложение в PWA-режиме (standalone)
     * @returns {boolean} true, если PWA установлен
     */
    isStandalone: () => {
        return (
            ('standalone' in navigator && navigator.standalone) || // iOS
            window.matchMedia('(display-mode: standalone)').matches // Chrome/Edge
        );
    },
};

/**
* 	Получает размер данных с автоматическим определением единиц измерения
* 	@param {any} data - Данные для измерения
* 	@param {object} [options] - Настройки
* 	@param {number} [options.limit] - Лимит в байтах (для предупреждения)
* 	@param {boolean} [options.log] - Выводить ли сообщение в консоль
* 	@returns {string} - Размер в формате "123.45 KB"
	Автоматический подбор единиц (от байтов до GB)

	С проверкой лимита (100 KB)
	Выбросит ошибку если > 100 KB
	getMessageSize(newConfig, { limit: 100 * 1024 });
		 
	Тихий режим (без логов):
	getMessageSize(newConfig, { log: false }).formatted;
			
	Автоматическое логирование:
	Выведет в консоль: "Размер данных: 455.00 bytes"
	cons size = getMessageSize(newConfig, { log: true });
*/
export function getMessageSize(data, options = {}) {
	const bytes = new Blob([JSON.stringify(data)]).size;
	const units = ['bytes', 'KB', 'MB', 'GB'];
			
	let size = bytes;
	let unitIndex = 0;
			
	while (size >= 1024 && unitIndex < units.length - 1) {
		size /= 1024;
		unitIndex++;
	}
			
	const formattedSize = size.toFixed(2) + ' ' + units[unitIndex];
			
	// Проверка лимита
	if (options.limit && bytes > options.limit) {
		const errorMsg = `Превышен лимит размера: ${formattedSize} (${bytes} bytes)`;
		if (options.log !== false) {
			log('[SB] ' + errorMsg, 'error');
		}
		throw new Error(errorMsg);
	}
			
	// Дополнительный вывод
	if (options.log) {
		log(`Размер данных для отправки в [SW] - (Рекомендуемый размер: < 100 KB) Текущий: ${formattedSize}`,'info','[PWA:HELPERS]');
	}
			
	return {
		bytes,      							// Исходный размер в байтах
		formatted: formattedSize,  				// Форматированная строка
		unit: units[unitIndex]     				// Текущая единица измерения
	};
}

// Получить текущюю тему
// const theme = getPreferredTheme();
export function getPreferredTheme() {
	// 1. Проверка localStorage
	if (typeof localStorage !== 'undefined') {
		const savedTheme = localStorage.getItem('theme');
		if (savedTheme === 'dark' || savedTheme === 'light') {
			return savedTheme;
		}
	}

	// 2. Проверка атрибутов HTML
	if (typeof document !== 'undefined' && document.documentElement) {
		const htmlTheme = document.documentElement.getAttribute('data-bs-theme') ||
		                  document.documentElement.getAttribute('data-theme');
		
		if (htmlTheme === 'dark') return 'dark';
		if (htmlTheme === 'light') return 'light';
	}

	// 3. Системные предпочтения
	if (typeof window !== 'undefined' && window.matchMedia) {
		if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
			return 'dark';
		}
	}

	// 4. По умолчанию
	return 'light';
}
