{{-- Поле поиска > 991 px. --}}
@if(isset($searchShowLg) && $searchShowLg)
    <div
        id="search_lg"
        class="flex-fill d-none d-lg-block pe-4 pe-xl-5 desktop-search-wrapper"
        data-component="search_lg"
        data-props='@json($globalSearchConfigJson , JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)'
        data-device-type="desktop"
    ></div>
@endif
