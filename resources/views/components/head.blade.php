<meta charset="utf-8">

{{--
	width=device-width 	— устанавливает ширину страницы равной ширине экрана устройства, чтобы контент не сжимался или не растягивался.
	initial-scale=1 	— задает начальный масштаб страницы (по умолчанию 100%).
	minimum-scale=1 	— запрещает уменьшение страницы ниже 100%.
	maximum-scale=1 	— запрещает увеличение страницы больше 100%.
	viewport-fit=cover 	— включает поддержку safe area для устройств с вырезами (например, iPhone с "челкой").
--}}
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

<title>@yield('meta_title', $title ?? app('settings')->get('site_name_' . app()->getLocale(), config('settings.site_name')))</title>

{{--
	Часть SEO-оптимизации сайта, и его стоит настраивать для каждой страницы индивидуально.
	Поисковые системы (Google, Яндекс и др.) используют это описание в сниппетах результатов поиска
	Социальные сети могут использовать это описание при расшаривании страницы
	Помогает пользователям понять содержание страницы до перехода на неё.
	Рекомендации по использованию:
	Описание должно быть уникальным для каждой страницы
	Длина обычно 150-160 символов (Google обрезает длинные описания)
	Должно содержать ключевые слова, но читаться естественно
	Должно точно отражать содержание страницы
--}}
<meta name="description" content="@yield('meta_description', $description ?? app('settings')->get('meta_description_' . app()->getLocale(), config('settings.meta_description')))">

{{--
	Исторически использовался поисковыми системами для понимания тематики страницы
	Содержит перечень ключевых слов и фраз, разделенных запятыми
	В современном SEO имеет значительно меньший вес, чем раньше
	Основные поисковики (Google, Bing) практически не учитывают этот тег при ранжировании
	Некоторые системы (Яндекс, внутренние поиски) могут его учитывать
	Может быть полезен для внутренней аналитики
	5-10 релевантных ключевых фраз
	Без спама и повторений
	Делаем уникальным для каждой страницы.
--}}
<meta name="keywords" content="@yield('meta_keywords', app('settings')->get('meta_keywords_' . app()->getLocale(), config('settings.meta_keywords')))">

{{--
	Этот метатег используется для указания, что веб-приложение может работать в полноэкранном
	режиме на мобильных устройствах (PWA - Progressive Web App).

	Указывает браузеру, что сайт является веб-приложением
		Позволяет запускать сайт в полноэкранном режиме (без адресной строки браузера)
		Аналог метатега  < meta name="apple-mobile-web-app-capable"> для iOS
	Как это работает:
		При добавлении сайта на домашний экран мобильного устройства
		При открытии через иконку приложения
		Сайт открывается без элементов браузера (как нативное приложение)
	Совместимость:
		Поддерживается большинством современных мобильных браузеров
		Особенно хорошо работает в Chrome для Android
		Для iOS нужно использовать аналогичный тег apple-mobile-web-app-capable
	Требования для полноценной работы:
		Наличие манифеста (manifest.json)
		Регистрация Service Worker
		Поддержка HTTPS
	Особенности поведения:
		В Android Chrome может скрывать адресную строку
		На iOS требует дополнительной настройки
		Не заменяет настоящий Web App Manifest
--}}

{{-- Для Android/универсальных браузеров --}}
<meta name="mobile-web-app-capable" content="yes">

{{-- Для iOS Safari --}}
<meta name="apple-mobile-web-app-capable" content="yes">

{{--
	default 			- стандартный серый фон (по умолчанию)
	black 				- черный фон
	black-translucent 	- полупрозрачный черный фон (контент под ним)
--}}
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

{{-- --}}
<meta name="theme-color" content="#000000">

{{-- Web App Manifest --}}
<link rel="manifest" href="/manifest.json">

{{-- Иконки для разных платформ --}}
<link rel="icon" type="image/png" href="{{-- $fileservice->getSystemFileUrl('icons', 'icon-32x32.png') --}}" sizes="32x32">
<link rel="apple-touch-icon" href="{{-- $fileservice->getSystemFileUrl('icons', 'icon-180x180.png') --}}">

{{-- Open Graph (для соцсетей / мессенджеров) --}}
<meta property="og:title" content="@yield('og_title', $title ?? app('settings')->get('site_name_' . app()->getLocale(), config('settings.site_name')))">
<meta property="og:description" content="@yield('og_description', $description ?? app('settings')->get('meta_description_' . app()->getLocale(), config('settings.meta_description')))">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{--@yield('og_image', $fileservice->getSystemFileUrl('icons', 'og-default.png'))--}}">

{{-- Twitter Cards (для Twitter / X) --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('twitter_title', $title ?? app('settings')->get('site_name_' . app()->getLocale(), config('settings.site_name')))">
<meta name="twitter:description" content="@yield('twitter_description', $description ?? app('settings')->get('meta_description_' . app()->getLocale(), config('settings.meta_description')))">
<meta name="twitter:image" content="{{--@yield('twitter_image', $fileservice->getSystemFileUrl('icons', 'twitter-default.png'))--}}">

{{-- Canonical URL (чтобы избежать дублей в SEO) --}}
<link rel="canonical" href="{{ url()->current() }}">

{{-- hreflang (если будет мультиязычность) --}}
<link rel="alternate" href="{{ url()->current() }}" hreflang="{{ app()->getLocale() }}">

@php
    //dd(app('settings')->get('meta_keywords_' . app()->getLocale(), config('settings.meta_keywords')));
    //dd(app('settings')->get('meta_description_' . app()->getLocale(), config('settings.meta_description')));
    //dd(app('settings')->get('site_name_' . app()->getLocale(), config('settings.site_name')));
@endphp
