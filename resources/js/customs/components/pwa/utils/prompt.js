/**
	Установить сайт как приложения - 29.05.2025-GX
**/
import { log } from '../utils/logger.js';
import { Environment } from '../utils/helpers_pwa.js';
import { StorageManager } from '../utils/storage.js';

export const Prompt = (confSR = {}) => {
	const px = '[PWA:PROMPT]';

	// Проверка пустого объекта
	if (!confSR || Object.keys(confSR).length === 0){
		log(`Нет двнных.`, 'error', px);
		return;
	}
	
	// Инициализация - Менеджер хранилища
	const storage = StorageManager(confSR);
	
	// Логика установки PWA
	let deferredPrompt = null;

    // 
    const showPrompt = () => {

		const isSafari = Environment.browser === 'Safari';
		const isAndroid = Environment.os === 'Android';
		const isWindows = Environment.os === 'Windows';
		
		const promptId = 'pwa-install-prompt';

        if (document.getElementById('pwa-install-prompt')) {
			log('Prompt already shown', 'warn', px);
			return;
		}

		// Подготовка текста с учетом платформы
		const title = isWindows ? confSR.ui.installTitle: confSR.ui.installTitle1;
			
		const text = isSafari
				? confSR.ui.installText.safari
				: isAndroid
					? confSR.ui.installText.android
					: confSR.ui.installText.default;
					
		// Создание HTML модального окна
		const promptHTML = `
		<div class="modal fade ${confSR.ui.animation}" id="${promptId}" tabindex="-1" aria-labelledby="${promptId}Label">
		<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg">
		<div class="modal-body text-center p-4">
				
			<div class="mb-3">
				${isWindows 
					?	`<svg xmlns="http://www.w3.org/2000/svg" width="195" height="195" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 825.84998 506.44" role="img" artist="Katerina Limpitsouni" source="https://undraw.co/"><path d="m438.22,378.66c-1.76999,10.70999-11.09,18.91-22.29001,18.91s-20.53-8.20001-22.29001-18.91h-57.04001v123.61002h158.66v-123.60999h-57.04001l.00003-.00003Z" fill="#d9d9d9" stroke-width="0"/><rect x="339.35999" y="501.81" width="15.68002" height="2.76999" fill="#b6b3c5" stroke-width="0"/><rect x="478.19" y="502.26999" width="15.67999" height="2.76999" fill="#b6b3c5" stroke-width="0"/><path d="m735.01001,381.89001H96.85c-4.88,0-8.85-3.97-8.85-8.85001V8.85c0-4.88,3.97-8.85,8.85-8.85h638.15995c4.88,0,8.84998,3.97,8.84998,8.85v364.20002c0,4.88-3.96997,8.85001-8.84998,8.85001h0l.00006-.01001Z" fill="#2f2e41" stroke-width="0"/><rect x="104.14" y="15.68" width="624.49001" height="352.36999" fill="#fff" stroke-width="0"/><path d="m0,505.53c0,.5.39999.91.91.91h824.03c.5,0,.90997-.39999.90997-.91s-.39996-.91-.90997-.91H.91c-.5,0-.91.39999-.91.91Z" fill="#2f2e43" stroke-width="0"/><path d="m184.09482,101.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#6c63ff" stroke-width="0"/><path d="m262.09482,101.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#6c63ff" stroke-width="0"/><path d="m341.09482,101.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#6c63ff" stroke-width="0"/><path d="m421.09482,101.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m499.09482,101.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m578.09482,101.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m656.09482,101.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m184.09482,162.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m262.09482,162.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m341.09482,162.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m421.09482,162.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m499.09482,162.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m578.09482,162.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m656.09482,162.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m184.09482,223.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m262.09482,223.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m341.09482,223.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m421.09482,223.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m499.09482,223.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m578.09482,223.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><path d="m656.09482,223.99967h-16.83627c-4.04324,0-7.33186-3.34082-7.33186-7.44824v-17.10352c0-4.10742,3.28862-7.44824,7.33186-7.44824h16.83627c4.04324,0,7.33186,3.34082,7.33186,7.44824v17.10352c0,4.10742-3.28862,7.44824-7.33186,7.44824Z" fill="#d9d9d9" stroke-width="0"/><circle cx="267.32753" cy="134.19903" r="7" fill="#ff6363" stroke-width="0"/></svg>`
					:   `<svg xmlns="http://www.w3.org/2000/svg" width="195" height="195" viewBox="0 0 738 729.04651" xmlns:xlink="http://www.w3.org/1999/xlink" role="img" artist="Katerina Limpitsouni" source="https://undraw.co/"><path d="M781.44466,258.424h-3.99878V148.87868a63.40186,63.40186,0,0,0-63.4018-63.40193H481.95735a63.40186,63.40186,0,0,0-63.402,63.4017v600.9744a63.40189,63.40189,0,0,0,63.4018,63.40191H714.04378a63.40187,63.40187,0,0,0,63.402-63.40167V336.40024h3.99878Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M763.95107,149.32105v600.09a47.35073,47.35073,0,0,1-47.35,47.35h-233.2a47.35085,47.35085,0,0,1-47.35-47.35v-600.09a47.35089,47.35089,0,0,1,47.35-47.35h28.29a22.50661,22.50661,0,0,0,20.83,30.99h132.96a22.50674,22.50674,0,0,0,20.83-30.99h30.29A47.35088,47.35088,0,0,1,763.95107,149.32105Z" transform="translate(-231 -85.47675)" fill="#fff"/><path d="M535.60344,266.51629h-35.8121a11.14181,11.14181,0,0,1-11.12921-11.12921V219.57515a11.1418,11.1418,0,0,1,11.12921-11.12921h35.8121a11.1418,11.1418,0,0,1,11.1292,11.12921v35.81193A11.1418,11.1418,0,0,1,535.60344,266.51629Z" transform="translate(-231 -85.47675)" fill="#6c63ff"/><path d="M617.90712,266.51629H582.095a11.1418,11.1418,0,0,1-11.12921-11.12921V219.57515A11.1418,11.1418,0,0,1,582.095,208.44594h35.8121a11.1418,11.1418,0,0,1,11.1292,11.12921v35.81193A11.1418,11.1418,0,0,1,617.90712,266.51629Z" transform="translate(-231 -85.47675)" fill="#6c63ff"/><path d="M700.2108,266.51629H664.3987a11.1418,11.1418,0,0,1-11.12921-11.12921V219.57515a11.1418,11.1418,0,0,1,11.12921-11.12921h35.8121A11.1418,11.1418,0,0,1,711.34,219.57515v35.81193A11.1418,11.1418,0,0,1,700.2108,266.51629Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M535.60344,358.025h-35.8121a11.1418,11.1418,0,0,1-11.12921-11.12921V311.08384a11.14181,11.14181,0,0,1,11.12921-11.12921h35.8121a11.1418,11.1418,0,0,1,11.1292,11.12921v35.81193A11.1418,11.1418,0,0,1,535.60344,358.025Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M617.90712,358.025H582.095a11.1418,11.1418,0,0,1-11.12921-11.12921V311.08384A11.1418,11.1418,0,0,1,582.095,299.95463h35.8121a11.1418,11.1418,0,0,1,11.1292,11.12921v35.81193A11.1418,11.1418,0,0,1,617.90712,358.025Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M700.2108,358.025H664.3987a11.1418,11.1418,0,0,1-11.12921-11.12921V311.08384a11.1418,11.1418,0,0,1,11.12921-11.12921h35.8121A11.1418,11.1418,0,0,1,711.34,311.08384v35.81193A11.1418,11.1418,0,0,1,700.2108,358.025Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M535.60344,449.51629h-35.8121a11.14181,11.14181,0,0,1-11.12921-11.12921V402.57515a11.1418,11.1418,0,0,1,11.12921-11.12921h35.8121a11.1418,11.1418,0,0,1,11.1292,11.12921v35.81193A11.1418,11.1418,0,0,1,535.60344,449.51629Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M617.90712,449.51629H582.095a11.1418,11.1418,0,0,1-11.12921-11.12921V402.57515A11.1418,11.1418,0,0,1,582.095,391.44594h35.8121a11.1418,11.1418,0,0,1,11.1292,11.12921v35.81193A11.1418,11.1418,0,0,1,617.90712,449.51629Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M700.2108,449.51629H664.3987a11.1418,11.1418,0,0,1-11.12921-11.12921V402.57515a11.1418,11.1418,0,0,1,11.12921-11.12921h35.8121A11.1418,11.1418,0,0,1,711.34,402.57515v35.81193A11.1418,11.1418,0,0,1,700.2108,449.51629Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M535.60344,541.025h-35.8121a11.1418,11.1418,0,0,1-11.12921-11.12921V494.08384a11.14181,11.14181,0,0,1,11.12921-11.12921h35.8121a11.1418,11.1418,0,0,1,11.1292,11.12921v35.81193A11.1418,11.1418,0,0,1,535.60344,541.025Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M617.90712,541.025H582.095a11.1418,11.1418,0,0,1-11.12921-11.12921V494.08384A11.1418,11.1418,0,0,1,582.095,482.95463h35.8121a11.1418,11.1418,0,0,1,11.1292,11.12921v35.81193A11.1418,11.1418,0,0,1,617.90712,541.025Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M700.2108,541.025H664.3987a11.1418,11.1418,0,0,1-11.12921-11.12921V494.08384a11.1418,11.1418,0,0,1,11.12921-11.12921h35.8121A11.1418,11.1418,0,0,1,711.34,494.08384v35.81193A11.1418,11.1418,0,0,1,700.2108,541.025Z" transform="translate(-231 -85.47675)" fill="#e6e6e6"/><path d="M640.94146,530.4129h-35.812a11.64255,11.64255,0,0,1-11.62939-11.62939v-35.812a11.64246,11.64246,0,0,1,11.62939-11.62891h35.812a11.64224,11.64224,0,0,1,11.62915,11.62891v35.812A11.64234,11.64234,0,0,1,640.94146,530.4129Z" transform="translate(-231 -85.47675)" fill="#6c63ff"/><path d="M471.496,622.82844a10.342,10.342,0,0,0,3.69829-15.421l7.73338-75.61094H466.215l-6.14591,73.83019A10.3981,10.3981,0,0,0,471.496,622.82844Z" transform="translate(-231 -85.47675)" fill="#ffb8b8"/><circle cx="255.84119" cy="380.94671" r="23.64504" fill="#ffb8b8"/><path d="M491.29262,575.82991a4.32432,4.32432,0,0,1-4.16765-3.1758c-1.89438-6.88278-6.82308-18.51986-14.64882-34.58971a28.65183,28.65183,0,0,1,16.27057-39.55789h0a28.63888,28.63888,0,0,1,36.80115,18.24393c6.0658,19.01719,5.773,39.38819,5.3654,47.26282a4.34283,4.34283,0,0,1-3.40214,4.00971l-35.28941,7.70634A4.31078,4.31078,0,0,1,491.29262,575.82991Z" transform="translate(-231 -85.47675)" fill="#ccc"/><polygon points="294.466 714.884 306.269 714.883 311.884 669.359 294.464 669.36 294.466 714.884" fill="#ffb8b8"/><path d="M522.93733,796.98753h37.09374a0,0,0,0,1,0,0v14.33167a0,0,0,0,1,0,0H537.269a14.33167,14.33167,0,0,1-14.33167-14.33167v0A0,0,0,0,1,522.93733,796.98753Z" transform="translate(852.00511 1522.80528) rotate(179.99738)" fill="#2f2e41"/><polygon points="176.447 699.587 186.79 705.273 213.644 668.087 198.379 659.695 176.447 699.587" fill="#ffb8b8"/><path d="M401.10879,788.93951h37.09375a0,0,0,0,1,0,0v14.33167a0,0,0,0,1,0,0H415.44044a14.33165,14.33165,0,0,1-14.33165-14.33165v0A0,0,0,0,1,401.10879,788.93951Z" transform="translate(172.861 1610.43142) rotate(-151.19904)" fill="#2f2e41"/><path d="M524.91492,775.09208a4.33167,4.33167,0,0,1-4.292-3.77467L505.86247,657.57526a2.4068,2.4068,0,0,0-4.49435-.85271L439.85251,768.32964a4.3558,4.3558,0,0,1-5.336,1.95832L421.108,765.17923a4.33179,4.33179,0,0,1-2.47022-5.68128l42.61335-104.682a3.33683,3.33683,0,0,0,.21764-.8292c5.77177-44.67273,20.457-67.39033,24.82022-73.28878a3.35482,3.35482,0,0,0,.47736-3.05452l-.59981-1.79943a4.32994,4.32994,0,0,1,.83367-4.21372c15.72223-17.90783,43.12549-8.136,43.40048-8.03539l.13726.05076.086.11752c30.72713,42.14,19.36622,179.00964,16.77683,206.019a4.32027,4.32027,0,0,1-3.97985,3.89877l-18.16966,1.39893C525.13844,775.08832,525.02633,775.09208,524.91492,775.09208Z" transform="translate(-231 -85.47675)" fill="#2f2e41"/><path d="M578.7642,493.88965a10.13308,10.13308,0,0,0-.23037,1.579l-41.35538,23.85776-10.052-5.78677-10.716,14.02882L533.21,539.54211a7.70167,7.70167,0,0,0,9.24024-.22512l42.6445-33.64254a10.10562,10.10562,0,1,0-6.33049-11.7848Z" transform="translate(-231 -85.47675)" fill="#ffb8b8"/><path d="M535.7959,520.45634,522.43,537.77923a4.3322,4.3322,0,0,1-6.55018.35878l-15.13115-15.71252a12.03133,12.03133,0,0,1,14.755-19.00779l18.97778,10.61169a4.3322,4.3322,0,0,1,1.31446,6.42695Z" transform="translate(-231 -85.47675)" fill="#ccc"/><path d="M483.99133,550.55265l-21.53172-3.88793a4.33221,4.33221,0,0,1-3.33464-5.64922l6.98015-20.66671a12.03133,12.03133,0,0,1,23.667,4.34515l-.681,21.73247a4.3322,4.3322,0,0,1-5.09977,4.12624Z" transform="translate(-231 -85.47675)" fill="#ccc"/><path d="M490.58363,460.98481c6.07728,4.15938,14.05689,8.435,20.2639,4.00558a11.29522,11.29522,0,0,0,3.707-13.02047c-2.97212-8.4993-11.138-12.2411-18.94654-15.10926-10.15117-3.72861-21.20429-6.69488-31.68372-4.02464S444.1834,445.65244,446.07514,456.3c1.52129,8.56253,9.59817,15.68722,8.45156,24.30792-1.154,8.676-10.91111,13.24287-19.52511,14.79349s-18.25326,2.04884-24.44381,8.23614c-7.89716,7.893-5.9251,22.00094,1.50581,30.33441s18.64456,12.111,29.51059,14.67893c14.39682,3.40241,29.96811,5.19052,43.64145-.45612s24.31376-20.84675,20.35281-35.1c-1.6731-6.02056-5.61442-11.10875-9.42349-16.06227s-7.66612-10.13345-9.1191-16.21089c-1.21082-5.06453-.31464-10.94469,3.04389-14.74367a4.27156,4.27156,0,0,0,.57223-4.993Z" transform="translate(-231 -85.47675)" fill="#2f2e41"/><polygon points="500 716.459 487.74 716.459 481.908 669.171 500.002 669.171 500 716.459" fill="#ffb8b8"/><path d="M478.98321,712.95555h23.64387a0,0,0,0,1,0,0v14.88687a0,0,0,0,1,0,0H464.09635a0,0,0,0,1,0,0v0A14.88686,14.88686,0,0,1,478.98321,712.95555Z" fill="#2f2e41"/><polygon points="546 716.459 533.74 716.459 527.908 669.171 546.002 669.171 546 716.459" fill="#ffb8b8"/><path d="M524.98321,712.95555h23.64387a0,0,0,0,1,0,0v14.88687a0,0,0,0,1,0,0H510.09635a0,0,0,0,1,0,0v0A14.88686,14.88686,0,0,1,524.98321,712.95555Z" fill="#2f2e41"/><path d="M775.50276,661.09729a10.7427,10.7427,0,0,1-2.06222-16.343L765.368,530.19642l23.253,2.25509.63868,112.18665a10.80091,10.80091,0,0,1-13.757,16.45913Z" transform="translate(-231 -85.47675)" fill="#ffb8b8"/><path d="M730.421,778.113l-13.49634-.64356a4.499,4.499,0,0,1-4.28589-4.46289l-.94189-136.55664a4.50111,4.50111,0,0,1,5.14648-4.48535l53.99366,7.83789a4.47382,4.47382,0,0,1,3.85351,4.41992l6.94434,126.53418a4.50047,4.50047,0,0,1-4.5,4.53418H762.58479a4.47888,4.47888,0,0,1-4.44531-3.80078l-8.97705-57.06738a3.5,3.5,0,0,0-6.93287.12793l-7.12622,59.60254a4.5171,4.5171,0,0,1-4.46875,3.96582Q730.52839,778.1189,730.421,778.113Z" transform="translate(-231 -85.47675)" fill="#2f2e41"/><path d="M739.922,644.22827c-11.89942-6.61133-21.197-8.34863-25.67993-8.7959a4.418,4.418,0,0,1-3.05347-1.67285,4.47791,4.47791,0,0,1-.93115-3.40137l12.9375-96.05078a33.21917,33.21917,0,0,1,19.36352-25.957,32.30589,32.30589,0,0,1,31.39551,2.46094q.665.44238,1.30518.90332a33.17817,33.17817,0,0,1,12.63647,34.57324c-7.93359,32.45508-10.65869,85.66211-11.12451,95.999a4.46544,4.46544,0,0,1-2.918,4.00488,45.08471,45.08471,0,0,1-15.22583,2.71094A38.12461,38.12461,0,0,1,739.922,644.22827Z" transform="translate(-231 -85.47675)" fill="#6c63ff"/><path d="M770.63178,568.68944a4.4817,4.4817,0,0,1-1.85872-3.40066l-1.70384-30.87614a12.39862,12.39862,0,0,1,24.34642-3.92684l7.48456,27.6049a4.50507,4.50507,0,0,1-3.16561,5.52077L774.444,569.384A4.48288,4.48288,0,0,1,770.63178,568.68944Z" transform="translate(-231 -85.47675)" fill="#6c63ff"/><circle cx="519.74361" cy="385.27072" r="24.56103" fill="#ffb8b8"/><path d="M661.73989,519.00209a10.52582,10.52582,0,0,1,.23929,1.64013l42.95745,24.782,10.44142-6.01094,11.13116,14.57228L704.17207,569.9061l-49.00791-38.66269a10.49579,10.49579,0,1,1,6.57573-12.24132Z" transform="translate(-231 -85.47675)" fill="#ffb8b8"/><path d="M706.47091,543.31688a4.48168,4.48168,0,0,1,1.29315-3.65337l21.8634-21.86849a12.39863,12.39863,0,0,1,19.16808,15.51623l-15.57,23.9922a4.50508,4.50508,0,0,1-6.22447,1.32511L708.49681,546.62A4.48287,4.48287,0,0,1,706.47091,543.31688Z" transform="translate(-231 -85.47675)" fill="#6c63ff"/><path d="M768.12421,490.179c-4.582,4.88078-13.09132,2.26067-13.68835-4.40717a8.05557,8.05557,0,0,1,.01013-1.5557c.30826-2.95357,2.01461-5.635,1.60587-8.7536a4.59045,4.59045,0,0,0-.8401-2.14891c-3.65125-4.88934-12.22228,2.18687-15.6682-2.2393-2.113-2.714.3708-6.98712-1.25066-10.0205-2.14005-4.00358-8.47881-2.0286-12.45387-4.22116-4.42276-2.43948-4.15822-9.22524-1.24686-13.35269,3.55052-5.03359,9.77572-7.71951,15.92335-8.10661s12.25292,1.27475,17.9923,3.51145c6.52108,2.54134,12.98768,6.05351,17.00066,11.78752,4.88022,6.97317,5.34986,16.34794,2.90917,24.50175C776.933,480.13416,771.86587,486.19335,768.12421,490.179Z" transform="translate(-231 -85.47675)" fill="#2f2e41"/><path d="M968,814.52325H232a1,1,0,0,1,0-2H968a1,1,0,0,1,0,2Z" transform="translate(-231 -85.47675)" fill="#ccc"/></svg>`
				}
			</div>
	 
			<h5 class="pt-1" id="${promptId}Label">${title}</h5>
			<p class="text-muted mb-4">${text}</p>

			<div class="d-flex flex-column align-items-center gap-3 pt-4">
				${isSafari 
				?   
					`<div class="d-flex justify-content-center gap-3 w-100">
						<button type="button" class="btn btn-secondary pe-3 w-100" id="pwa-later-btn">
							<i class="${confSR.buttons.later.icon}"></i>
							${confSR.buttons.later.text}
						</button>
						<button type="button" class="btn btn-outline-secondary pe-3 w-100" id="pwa-dismiss-btn">
							<i class="${confSR.buttons.dismiss.icon}"></i>
							${confSR.buttons.dismiss.text}
						</button>
					</div>` 
				:   
					`<div class="d-flex justify-content-center gap-3 w-100">
						<button type="button" class="btn btn-primary w-100" id="pwa-install-btn">
							<i class="${confSR.buttons.install.icon}"></i>
							${confSR.buttons.install.text}
						</button>
						<button type="button" class="btn btn-secondary w-100" id="pwa-later-btn">
							<i class="${confSR.buttons.later.icon}"></i>
							${confSR.buttons.later.text}
						</button>
					</div>
					<button type="button" class="btn btn-outline-secondary border-0 mb-n1" id="pwa-dismiss-btn">
						<i class="${confSR.buttons.dismiss.icon}"></i>
						${confSR.buttons.dismiss.text}
				</button>`
				}
			</div>
		</div>
		</div>
		</div>
		</div>`;

		// Вставка в DOM
		document.body.insertAdjacentHTML('beforeend', promptHTML);
		const modalEl = document.getElementById(promptId);
			
		// Инициализация модального окна
		let modalInstance;
		
		// Чтобы скрипт не падал, если Bootstrap не загружен.
		try {
			// Чтобы скрипт не падал, если Bootstrap не загружен.
			if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
				modalInstance = new bootstrap.Modal(modalEl, {
					backdrop: 'static',  // Предотвращает закрытие кликом вне модалки
					keyboard: false      // Предотвращает закрытие клавишей ESC
				});
			} else {
				throw new Error('Bootstrap Modal not found');
			}
			/* if (typeof Modal === 'function') {
				modalInstance = new Modal(modalEl, {
					backdrop: 'static',  // Предотвращает закрытие кликом вне модалки
					keyboard: false      // Предотвращает закрытие клавишей ESC
				});
			} else {
				throw new Error('Bootstrap Modal not found');
			} */
		} catch (e) {
			log('Modal initialization failed' + e, 'error', px);
			// Фолбэк для IE11 и старых браузеров
			const fallbackClose = () => modalEl.style.display = 'none';
			return {
				close: fallbackClose,
				show: () => modalEl.style.display = 'block'
			};
			//log('Modal initialization failed' + e, 'error', px);
			//modalEl.remove();
			//return { close: () => {} };
		}

		// Хранилище для обработчиков
        const eventHandlers = {
            buttons: new Map(),// Для кнопок
            modal: null,		// Для модального окна
            vibration: null
        };

		// Безопасное закрытие модального окна
		const safeHideModal = () => {
            try {
                if (modalInstance && modalEl && document.body.contains(modalEl)) {
                    modalInstance.hide();
                }
            } catch (e) {
                log('Error hiding modal' + e, 'error', px);
            }
        };

		// Оригинальный обработчик закрытия
        const originalHandleModalHide = () => {
            try {
                if (modalEl && document.body.contains(modalEl)) {
                    // Удаляем все обработчики
                    if (eventHandlers.modal) {
                        modalEl.removeEventListener('hidden.bs.modal', eventHandlers.modal);
                    }
                    
                    eventHandlers.buttons.forEach((handler, btn) => {
                        btn.removeEventListener('click', handler);
                    });
                    eventHandlers.buttons.clear();

                    if (eventHandlers.vibration) {
                        eventHandlers.vibration();
                    }

                    modalEl.remove();
                }
            } catch (e) {
                log('Error cleaning up modal' + e, 'error', px);
            } finally {
                modalInstance = null;
            }
        };
		
		// Обертка с вибрацией
        const handleModalHideWithVibration = () => {
            if (eventHandlers.vibration) {
                eventHandlers.vibration();
            }
            originalHandleModalHide();
        };
		
		// Настройка обработчиков кнопок
        const setupButtonHandler = (btnId, handler) => {
            const btn = document.getElementById(btnId);
            if (!btn) {
                log(`Button with id "${btnId}" not found`, 'error', px);
                return null;
            }

            const wrappedHandler = () => {
                try {
                    handler();
                    safeHideModal();
                } catch (e) {
                    log(`Error in ${btnId} handler: ${e}`, 'error', px);
                    safeHideModal();
                }
            };

            btn.addEventListener('click', wrappedHandler);
            eventHandlers.buttons.set(btn, wrappedHandler);
            return btn;
        };
		
		// Настройка вибрации
        const setupVibration = () => {
            if (!Environment.isMobile || !('vibrate' in navigator)) return;

            const vibrationHandlers = {
                interaction: () => {
                    document.documentElement.setAttribute('data-user-interacted', 'true');
                },
                vibrate: () => {
                    try {
                        if (document.documentElement.hasAttribute('data-user-interacted')) {
                            navigator.vibrate(100);
                        }
                    } catch (e) {
                        log(`Vibration failed: ${e}`, 'error', px);
                    }
                }
            };

            modalEl.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('mousedown', vibrationHandlers.interaction);
                btn.addEventListener('touchstart', vibrationHandlers.interaction);
                btn.addEventListener('click', vibrationHandlers.vibrate);
            });

            // Функция очистки
            return () => {
                modalEl.querySelectorAll('button').forEach(btn => {
                    btn.removeEventListener('mousedown', vibrationHandlers.interaction);
                    btn.removeEventListener('touchstart', vibrationHandlers.interaction);
                    btn.removeEventListener('click', vibrationHandlers.vibrate);
                });
            };
        };
		
		// Сохраняем функцию очистки вибрации
        eventHandlers.vibration = setupVibration();
		
		// Устанавливаем обработчики кнопок
        if (!isSafari) {
            setupButtonHandler('pwa-install-btn', () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt()
                        .then(choiceResult => {
                            storage.set(storage.keys.installed, choiceResult.outcome === 'accepted');
                            deferredPrompt = null;
                        })
                        .catch(err => {
                            log(`Prompt error: ${err}`, 'error', px);
                        });
                }
            });
        }
		
		// Обработчик для кнопки "Позже" (Напомнить позже)
		// Повторное предложение появится через confSR.remindAfterHours (по умолчанию 24 часа)
		setupButtonHandler('pwa-later-btn', () => {
            storage.set(storage.keys.timeout, Date.now());
        });

        // Обработчик для кнопки "Не предлагать" (Удалить навсегда)
        setupButtonHandler('pwa-dismiss-btn', () => {
            // главный флаг, который полностью отключает показ промпта
			storage.set(storage.keys.dismissed, true);
            // Защиту от случайного сброса флага dismissed
			// Даже если dismissed будет удалён/сброшен, таймаут сработает как вторая линия защиты
			// При следующей проверке shouldShowPrompt() сначала проверит dismissed, а затем таймаут
			storage.set(storage.keys.timeout, Date.now());
            log('PWA prompts permanently dismissed', null, px);
        });

        // Устанавливаем обработчик закрытия
        eventHandlers.modal = handleModalHideWithVibration;
        modalEl.addEventListener('hidden.bs.modal', eventHandlers.modal);
		
		// Показываем модальное окно
        try {
            modalInstance.show();
        } catch (e) {
            log('Error showing modal' + e, 'error', px);
            handleModalHideWithVibration();
        }

        // Возвращаем функцию для принудительного закрытия
        return {
            close: safeHideModal
        };
	};
	
	//
	const shouldShowPrompt = () => {
		if (Environment.isStandalone()) {
			log('App already installed');
			return false;
		}

		const lastShown = storage.get(storage.keys.timeout);
		const dismissed = storage.get(storage.keys.dismissed);
		const installed = storage.get(storage.keys.installed);

		if (dismissed || installed) return false;
		if (lastShown) {
			const hoursPassed = (Date.now() - lastShown) / (1000 * 60 * 60);
			return hoursPassed >= confSR.remindAfterHours;
		}
		return true;
	};
	
	// Обработчики событий установки
	const setupEventListeners = () => {

		// For Safari/iOS - show custom instructions
		if (Environment.browser === 'Safari' || Environment.os === 'iOS') {
			if (shouldShowPrompt()) {
				setTimeout(() => showPrompt(), confSR.promptDelay);
			}
			return;
		}

		// For other browsers
		window.addEventListener('beforeinstallprompt', (e) => {
			deferredPrompt = e;
			log('BeforeInstallPrompt event received', null, px);

			if (shouldShowPrompt()) {
				setTimeout(() => showPrompt(), confSR.promptDelay);
			}
		});

		window.addEventListener('appinstalled', () => {
			storage.set(storage.keys.installed, true);
			deferredPrompt = null;
			log('PWA successfully installed', null, px);
		});
	};
	
	//
	setupEventListeners();
	
	// For debugging purposes
	// Для тестирования без реальных событий.
    if (confSR.diagnostics) {
		window.debugPWA = {
			showPrompt,
			resetStorage: () => {
				storage.clear(storage.keys.timeout);
				storage.clear(storage.keys.dismissed);
				storage.clear(storage.keys.installed);
				log('Storage cleared');
			},
			simulateInstall: () => {
				storage.set(storage.keys.installed, true);
				log('Simulated install');
			},
			// Метод для эмуляции события установки
			simulateBeforeInstallPrompt: () => {
				const event = new Event('beforeinstallprompt');
				window.dispatchEvent(event);
				log('Simulated beforeinstallprompt event');
			}
		};
	}
}
