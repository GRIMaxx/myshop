// Импорт глобальных библиотек

// jQuery с защитой от повторной инициализации
import $ from 'jquery';
if (!window.$) {
    window.$ = window.jQuery = $;

    // Глобальные настройки jQuery
    $.ajaxSetup({
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
}
