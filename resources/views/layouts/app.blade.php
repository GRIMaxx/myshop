<!DOCTYPE html>
<html
    lang="{{  app()->getLocale() }}"
    data-bs-theme="{{ $theme ?? 'light' }}"
    data-pwa="{{ $pwa ?? false }}"
>
{{-- Подключить кеширования --}}
<head>

    {{--
        Подключаем метаданые + кеширование (при разработке кеш отключен)
        myshop:cache:   -- Место хранения кеша
        meta:           -- Тип хранимых данных
        s_cfg:          -- Сервис хоть и не на прямую но это настройки
        $store:         -- Это еквквалент темы сайта либо отдельный магазин "каталог в котором все сомпоненты *.blade.php закешированые"
        head_layout     -- Уникальный ключ (в какталоге $store он должен быть строго уникален)
        --
        Место хранения db=1
        Пример готового ключа в Redis который можно увидеть в Redis Insight или аналог:
        "myshop:cache:meta:s_cfg:default:head_layout"

        !Для стабильности и строгости не делать мусорку этот код как эталон но с разными ключами
    --}}
    @cache("meta:s_cfg:{$store}:head_layout", 3600)
        <x-head :title="$title" :description="$description" />
    @endcache

    {{-- Для тестов скомпилированный css<link rel="stylesheet" href="{{ 'assets/css/theme.min.css' }}" id="theme-styles">--}}

    @vite('resources/scss/app.scss') {{-- Собирает CSS/SCSS (Bootstrap overrides + кастомные стили) в один CSS-бандл.--}}

    {{--
            Основной JS + глобальные библиотеки - подключает главный JS, где импортированы jQuery, bootstrap, libs,
            React компоненты через React/index.tsx.
        --}}
    @vite('resources/js/app.tsx')

    {{-- React HMR - подключает HMR для всех React entry points, указанных в vite.config.js --}}
    @viteReactRefresh

</head>
<body>

    <!-- Customizer offcanvas ----------------------------------------------------------------------->
    <x-customizer-offcanvas />
    <!-- END Customizer offcanvas ------------------------------------------------------------------->

    <!-- Shopping cart offcanvas -------------------------------------------------------------------->
    <x-shopping-cart-offcanvas />
    <!-- END Shopping cart offcanvas ---------------------------------------------------------------->

    <!-- Navigation bar (Page header) --------------------------------------------------------------->
    <header class="navbar navbar-expand-lg navbar-dark bg-dark d-block z-fixed p-0"
        data-sticky-navbar="{&quot;offset&quot;: 500}"
    >
        <!-- Контейнер-Шапка темная тема ------------------------------------------------------------->
        <div class="container d-block py-1 py-lg-3" data-bs-theme="dark">
            <div class="navbar-stuck-hide pt-1"></div>
            <div class="row flex-nowrap align-items-center g-0">
                <!-- Левая часть --------------------------------------------------------------------->
                {{--col-lg-3 изменил (для увиличения длинны поиска поля ) --}}
                <div class="col col-lg-2 d-flex align-items-center">

                    <!-- Mobile offcanvas menu toggler (Hamburger) ----------------------------------->
                    <x-mobile-offcanvas-menu-toggler />
                    <!-- END Mobile offcanvas menu toggler (Hamburger) ------------------------------->

                    <!-- Navbar brand (Logo) --------------------------------------------------------->
                    @cache("meta:s_cfg:{$store}:navbar_brand_logo", 3600)
                        <x-navbar-brand-logo />
                    @endcache
                    <!-- END Navbar brand (Logo) ----------------------------------------------------->
                </div>
                <!--End Левая часть ------------------------------------------------------------------>
                <!-- Правая часть -------------------------------------------------------------------->
                {{--col-lg-9  изменил (для увиличения длинны поиска поля ) --}}
                <div class="col col-lg-10 d-flex align-items-center justify-content-end">
                    <!-- Search visible on screens lg ------------------------------------------------>
                    <x-search-lg />
                    <!-- End Search visible on screens lg -------------------------------------------->

                    <!-- Sale link visible on screens > 1200px wide (xl breakpoint) ------------------>
                    <x-sale-link-lg />
                    <!-- END Sale link visible on screens > 1200px wide (xl breakpoint) -------------->

                    <!-- Button group ---------------------------------------------------------------->
                    <div class="d-flex align-items-center">
                        <!-- Navbar stuck nav toggler ------------------------------------------------>
                        <x-navbar-stuck-nav-toggler />
                        <!-- END Navbar stuck nav toggler -------------------------------------------->

                        <!-- Theme switcher (light/dark/auto) ---------------------------------------->
                        <x-theme-switcher />
                        <!-- END Theme switcher (light/dark/auto) ------------------------------------>

                        <!-- Search toggle button visible on screens < 992px wide (lg breakpoint) ---->
                        <x-search-toggle-button-md />
                        <!-- END Search toggle button visible on screens < 992px wide(lg breakpoint)-->

                        <!-- Account button visible on screens > 768px wide (md breakpoint) ---------->
                        <x-account-button-lg />
                        <!-- END Account button visible on screens > 768px wide (md breakpoint) ------>

                        <!-- Wishlist button visible on screens > 768px wide (md breakpoint) --------->
                        <x-wishlist-button-lg />
                        <!-- END Wishlist button visible on screens > 768px wide (md breakpoint) ----->

                        <!-- Cart button ------------------------------------------------------------->
                        <x-cart-button-lg />
                        <!-- END Cart button --------------------------------------------------------->
                    </div>
                    <!-- END Button group ------------------------------------------------------------>
                </div>
                <!-- End Правая часть ---------------------------------------------------------------->
            </div>
            <div class="navbar-stuck-hide pb-1"></div>
        </div>
        <!-- Контейнер-Шапка темная тема ------------------------------------------------------------>

        <!-- Search visible on screens < 992px wide (lg breakpoint). It is hidden inside collapse by default -->
        <x-search-md />
        <!-- END Search visible on screens < 992px wide (lg breakpoint). It is hidden inside collapse by default -->

        <!-- Main navigation that turns into offcanvas on screens < 992px wide (lg breakpoint) ------>
        <div class="collapse navbar-stuck-hide" id="stuckNav">
            <nav class="offcanvas offcanvas-start" id="navbarNav" tabindex="-1" aria-labelledby="navbarNavLabel">

                <!-- 1 -->
                <x-close-offcanvas-button />
                <!-- 1 -->

                <div class="offcanvas-body py-3 py-lg-0">
                    <div class="container px-0 px-lg-3">
                        <div class="row">

                            <!-- Categories mega menu ----------------------------------------------->
                            <x-categories-mega-menu />
                            <!-- END Categories mega menu ------------------------------------------->

                            <!-- Navbar nav --------------------------------------------------------->
                            <div class="col-lg-9 d-lg-flex pt-3 pt-lg-0 ps-lg-0">
                                <x-navbar-nav-1 />
                                <hr class="d-lg-none my-3">
                                <x-navbar-nav-2 />
                            </div>
                            <!-- END Navbar nav ----------------------------------------------------->

                        </div>
                    </div>
                </div>

                <!-- 2 -->
                <x-account-links />
                <!-- 2 -->

            </nav>
        </div>
        <!-- END Main navigation that turns into offcanvas on screens < 992px wide (lg breakpoint)-->
    </header>
    <!-- END Navigation bar (Page header) ----------------------------------------------------------->

    <!-- Page content ------------------------------------------------------------------------------->
    <main class="content-wrapper">
        @yield('content')
    </main>
    <!-- END Page content --------------------------------------------------------------------------->

    <!-- Page footer -------------------------------------------------------------------------------->
    <footer class="footer position-relative bg-dark">
        <span class="position-absolute top-0 start-0 w-100 h-100 bg-body d-none d-block-dark"></span>
        <div class="container position-relative z-1 pt-sm-2 pt-md-3 pt-lg-4" data-bs-theme="dark">

            <!-- Columns with links that are turned into accordion on screens < 500px wide (sm breakpoint) -->
            <x-columns-with-links />
            <!-- END Columns with links that are turned into accordion on screens < 500px wide (sm breakpoint) -->

            <!-- Category / tag links ---------------------------------------------------------------->
            <x-category-tag-links />
            <!-- END Category / tag links ------------------------------------------------------------>

            <!-- Copyright + Payment methods --------------------------------------------------------->
            <x-copyright-payment-methods />
            <!-- End Copyright + Payment methods ----------------------------------------------------->

        </div>
    </footer>

    <!-- Back to top button ------------------------------------------------------------------------->
    <x-back-to-top-button />
    <!-- END Back to top button --------------------------------------------------------------------->

    {{-- Test php --}}
    @php
        //dd($store);
    @endphp

    {{-- Test js --}}
    <script>
        //window.addEventListener('load', () => {
            //console.log('Page is fully loaded');
            //console.log(window.bootstrap);  // Проверим, доступен ли объект
        //});
    </script>
</body>
</html>
