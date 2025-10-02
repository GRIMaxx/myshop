// Загрузка спрайта - icons.svg в DOM один раз
// Теперь спрайт будет вставлен внутрь DOM, и <use xlinkHref="#...">
// будет работать 100% стабильно без скачков и "непоявлений".
export default (() => {
	if (document.getElementById('__svg-sprite-injected__')) return;
	fetch('/assets/icons/icons.svg')
		.then(res => res.text())
		.then(sprite => {
			const div = document.createElement('div');
			div.id = '__svg-sprite-injected__';
			div.style.display = 'none';
			div.innerHTML = sprite;
			document.body.insertBefore(div, document.body.firstChild);
		});
})();

