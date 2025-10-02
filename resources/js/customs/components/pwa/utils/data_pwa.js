import {DEFAULT_CONFIG_PWA} from '../config/default_config_pwa.js';      

// Получает данные из атрибута data-props элемента #r-r-n-1
// Ищет элемент #r-r-n-1 и атрибут data-props.
// @returns {Object|null} Данные или null, если что-то пошло не так
export function getDataFromPage(rootId = '') {
    
	const px = '[PWA:DDOM]';
	
	// Нет id 
	if(rootId === ''){return null;}

	// Проверяем, что DOM полностью загружен
    if (typeof document === 'undefined' || !document.getElementById) {
        console.error(`${px} DOM не доступен`);
        return null;
    }

    // Ищем контейнер с данными
    const rootElement = document.getElementById(rootId);
    if (!rootElement) {
        console.error(`${px} Элемент #` + rootId + ` не найден`);
        return null;
    }

    // Извлекаем JSON-строку из data-props
    const propsString = rootElement.getAttribute("data-props");
    if (!propsString) {
        console.error(`${px} Атрибут data-pros пуст или отсутствует`);
        return null;
    }

    // Парсим JSON
    try {
        const props = JSON.parse(propsString);
        return props;
    } catch (error) {
        console.error(`${px} Ошибка парсинга JSON:`, error);
        return null;
    }
}