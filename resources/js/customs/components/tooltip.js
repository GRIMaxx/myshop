/** (02.10.2025)
 * Tooltip
 * @requires https://getbootstrap.com
 * @requires https://popper.js.org/
 */
export default (() => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');

    // Если элементы не найдены, прекращаем выполнение
    if (tooltipTriggerList.length === 0) return;

    // Инициализируем Tooltip для каждого элемента
    [...tooltipTriggerList].forEach((tooltipTriggerEl) => {
        new bootstrap.Tooltip(tooltipTriggerEl, { trigger: 'hover' });
    });
})();

