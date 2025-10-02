/**
 * Focus input field automatically upon openning modal, collapse, offcanvas, dropdown or window
 */

const initAutofocus = () => {
 
  
  const elements = document.querySelectorAll<HTMLElement>('[data-autofocus]');
  if (!elements.length) return;

  elements.forEach((el) => {
    const containerType = el.dataset.autofocus;
    const supportedTypes = ['modal', 'collapse', 'offcanvas', 'dropdown'];

    if (containerType && supportedTypes.includes(containerType)) {
      const container = el.closest(`.${containerType}`);
      container?.addEventListener(`shown.bs.${containerType}`, () => el.focus());
    } else {
      el.focus();
    }
  });
};

// Для автоматической инициализации при загрузке
document.addEventListener('DOMContentLoaded', initAutofocus);

// Экспорт для ручного вызова если нужно
//export default initAutofocus;