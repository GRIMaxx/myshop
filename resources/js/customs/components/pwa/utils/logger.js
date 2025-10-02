import { DEFAULT_CONFIG_PWA } from '../config/default_config_pwa.js'; 

/**
 * Улучшенный логгер для PWA с поддержкой строк и объектов
 * @param {string|object} message 	- Сообщение или объект для вывода
 * @param {string} type 			- Тип сообщения (log|warn|error|info|debug)
 * @param {string} prefix 			- Префикс в логах (default: '[PWA]')
 */
// Поддерживает вывод как строк, так и объектов (с красивым форматированием)
// Сохраняет все оригинальные фичи (стили, префикс, диагностику)
// Автоматически определяет тип данных и выбирает оптимальный способ вывода
export function log(message, type = 'log', prefix = '[PWA]') {

	if (!self.diagnostics || DEFAULT_CONFIG_PWA.logsOn == false) return;

    // Стили для console.log
    const styles = {
        log: 'color: inherit;',
        warn: 'color: orange; font-weight: bold;',
        error: 'color: red; font-weight: bold;',
        info: 'color: blue;',
        debug: 'color: #888;'
    };

    // Стиль для префикса
    const prefixStyle = 'color: #4CAF50; font-weight: bold;';
    const messageStyle = styles[type] || styles.log;

    // Форматирование в зависимости от типа данных
    if (typeof message === 'object' && message !== null) {
        // Вывод объекта с префиксом
        console.groupCollapsed(`%c${prefix}`, prefixStyle);
        console[type]('%cObject:', messageStyle, message);
        console.groupEnd();
    } else {
        // Вывод строки/числа/и т.д.
        const formattedMessage = `%c${prefix}%c ${message}`;
        try {
            console[type](formattedMessage, prefixStyle, messageStyle);
        } catch (e) {
            console.log(formattedMessage, prefixStyle, messageStyle);
        }
    }
}

// старая без обьекта
// Основная функция логирования
export function log1(message, diagnostics = false, type = 'log', prefix = '[PWA]') {
	
	// Проверяем доступность диагностики
	if (!diagnostics) {return;}

	// Добавляем стили для console.log (работает в большинстве современных браузеров)
	const styles = {
		log: 'color: inherit;',
		warn: 'color: orange; font-weight: bold;',
		error: 'color: red; font-weight: bold;',
		info: 'color: blue;',
		debug: 'color: #888;'
	};

    // Форматируем сообщение
	const formattedMessage = `%c${prefix}%c ${message}`;
	const swStyle = 'color: #4CAF50; font-weight: bold;';
	const messageStyle = styles[type] || styles.log;

	try {
		// Пытаемся использовать выбранный тип логирования
		console[type](formattedMessage, swStyle, messageStyle);
	} catch (e) {
		// Фолбэк на обычный console.log
		console.log(formattedMessage, swStyle, messageStyle);
	}
}