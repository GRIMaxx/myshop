{{--
    Поле поиска > 991 px.

    Переменныеобьявляються здесь : .\resources\views\layouts\app.blade.php
--}}
@php
// Test
@endphp
@if(isset($searchlg) && $searchlg)
    <div
        id="search_lg"
        class="flex-fill d-none d-lg-block pe-4 pe-xl-5 desktop-search-wrapper"
        data-component="search_lg"
        data-props='@json($globalSearchConfigJson)'
        data-device-type="desktop"
    ></div>
@endif
