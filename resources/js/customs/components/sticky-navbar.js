/**
 * Stuck navbar to the top on page scroll (02.10.2025)
 */
export default (() => {
    const navbar = document.querySelector('[data-sticky-navbar]');
    if (!navbar) return;

    const navbarHeight = navbar.clientHeight;

    // Функция для добавления/удаления классов к navbar
    const handleStickyNavbar = () => {
        let offsetValue = 200; // Значение по умолчанию

        try {
            // Пробуем распарсить offset из data-атрибута
            const { offset = 200 } = JSON.parse(navbar.dataset.stickyNavbar || '{}');
            offsetValue = parseInt(offset, 10);

            if (isNaN(offsetValue)) {
                throw new Error('Invalid offset value');
            }
        } catch (e) {
            console.warn('Invalid JSON in data-sticky-navbar:', navbar.dataset.stickyNavbar, e);
        }

        // Если пользователь прокрутил достаточно
        if (window.scrollY >= offsetValue && !navbar.classList.contains('navbar-stuck')) {
            document.body.style.paddingTop = `${navbarHeight}px`; // Добавляем отступ
            navbar.classList.add('fixed-top', 'navbar-stuck');
        } else if (window.scrollY < offsetValue && navbar.classList.contains('navbar-stuck')) {
            document.body.style.paddingTop = '0'; // Убираем отступ
            navbar.classList.remove('fixed-top', 'navbar-stuck');
        }
    };

    // Функция дебаунса для оптимизации обработчика скролла
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            const later = () => {
                timeout = null;
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Прикрепляем обработчик события скролла с дебаунсом
    window.addEventListener('scroll', debounce(handleStickyNavbar, 5));

    // Начальный вызов для установки состояния в зависимости от начальной позиции страницы
    handleStickyNavbar();
})();
