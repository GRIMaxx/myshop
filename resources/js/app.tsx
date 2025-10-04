// Собранно 03.10.2025 - GX

// SCSS импорт делай в app.scss, а JS здесь
import '../scss/app.scss'

// Импорт глобальных библиотек
import '@/libs';                                     // (index.) jQuery, ...., или bootstrap но одной строкой

// Модульные импорты конкретных компонентов bootstrap
import '@libs/bootstrap';                           // bootstrap JS (модули)

// Подключаем компоненты React
import { components } from '@js/react';             // (index.) Импортируем компоненты для дальнейшего использования

// Инициализация React с компонентами
import { createRoot } from 'react-dom/client';      // Подключаем React пакет для внедрения в HTML

// Подключаем кастомные скрипты из index.js
import '@js_customs';                               // (index.) Здесь будет подключение всех кастомных скриптов

// --

let needsReloadOnResize = false;
let lastWidth = window.innerWidth;

const elements = document.querySelectorAll('[data-component]') as NodeListOf<HTMLElement>;
elements.forEach(el => {

    // Определяем требуемый тип (mobile или desktop)
    // Где нужна проверка для компонеттов проверяем ширину перед запуском
    // пока тестирую на поиске
    const deviceType = el.getAttribute('data-device-type');
    const currentWidth = window.innerWidth;
    const isMobile = currentWidth < 992;

    const isWrongDeviceType =
        (deviceType === 'desktop' && isMobile) ||
        (deviceType === 'mobile' && !isMobile);

    if (isWrongDeviceType) {
        el.remove(); 							// Удаляем компонент, не соответствующий текущей ширине
        needsReloadOnResize = true;
        return;
    }

    // Получаем имя и компонент
    const name = el.dataset.component!;
    const Component = (components as any)[name];

    if (Component) {
        let props: any = {};
        if (el.dataset.props) {
            try {
                props = JSON.parse(el.dataset.props);
            } catch (e) {
                console.error('Failed to parse data-props JSON', e);
            }
        }
        createRoot(el).render(<Component {...props} />);
    } else {
        console.warn(`Component "${name}" not found.`);
    }
});

// Вешаем слушатель resize — если хоть один элемент был удалён
if (needsReloadOnResize) {
    window.addEventListener('resize', () => {
        const currentWidth = window.innerWidth;
        if (currentWidth !== lastWidth) {
            lastWidth = currentWidth;
            location.reload();
        }
    });
}
