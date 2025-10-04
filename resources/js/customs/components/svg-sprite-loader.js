// Загрузка спрайта - icons.svg в DOM один раз
// Теперь спрайт будет вставлен внутрь DOM, и <use xlinkHref="#...">
// будет работать 100% стабильно без скачков и "непоявлений".
export default (() => {
    // Проверка, если спрайт уже был загружен
    if (document.getElementById('__svg-sprite-injected__')) return;

    // Асинхронная загрузка спрайта
    fetch('/assets/icons/icons.svg')
        .then(res => {
            if (!res.ok) {
                throw new Error('Failed to load SVG sprite');
            }
            return res.text();
        })
        .then(sprite => {
            const div = document.createElement('div');
            div.id = '__svg-sprite-injected__';
            div.style.display = 'none';
            div.innerHTML = sprite;
            document.body.insertBefore(div, document.body.firstChild);
        })
        .catch(error => {
            console.error('Error loading SVG sprite:', error);
        });
})();


