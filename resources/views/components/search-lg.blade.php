<div class="position-relative flex-fill d-none d-lg-block pe-1 pe-xl-2">
    <div class="d-flex">

        {{-- Поле поиска --}}
        <div class="flex-grow-1">
            <div class="input-group">
                <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-lg text-white ms-3"></i>
                <input
                    id="search_lg"
                    type="text"
                    class="form-control form-control-lg form-icon-start form-icon-end rounded-end-0 rounded-start-gx border-end-0 s-control-gx"
                    placeholder="Dropdown on the right"
                    aria-label="Text input with dropdown addon"
                    aria-describedby="dropdown-addon-right"
                >
                <i class="ci-external-link position-absolute top-50 end-0 translate-middle-y fs-lg text-white me-3"></i>
            </div>

            {{-- Блок результатов поиска (пока статичный, всегда виден) --}}
            <ul
                id="search-results"
                class="list-group position-absolute w-100 mt-1 shadow-sm search-panel list-group search-results"
                style="z-index: 1050;"
            >
                <li class="list-group-item">
                    <!-- Левая часть: картинка -->
                    <div class="me-3 flex-shrink-0 search-icon-wrapper">
                        <img src="assets/img/home/electronics/banner/camera.png" alt="icon" class="search-thumb">
                        <!-- Вариант с иконкой (через <use>) -->
                        <!-- <svg class="search-thumb">
                        <use xlink:href="#mdi--plus-one"></use>
                        </svg> -->
                    </div>

                    <!-- Правая часть: контент -->
                    <div class="flex-grow-1">
                        <!-- Верхняя строка (иконки, бейджи и т.п.) -->
                        <div class="d-flex align-items-center small text-muted mb-1">
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <i class="ci-star me-2 text-warning"></i>
                            <i class="ci-heart me-2 text-danger"></i>
                            <i class="ci-tag me-2"></i>
                            <span class="badge bg-secondary me-2">Категория</span>
                            <span class="badge bg-info me-2">New</span>
                        </div>

                        <!-- Заголовок -->
                        <div class="fw-semibold text-truncate mb-1">
                            Название товара или длинный заголовок, который обрежется 40px
                        </div>

                        <!-- Дополнительный текст -->
                        <div class="text-muted small">
                            Здесь может быть описание товара, категория или что-то ещё.
                        </div>
                    </div>
                </li>

            </ul>
        </div>

        {{-- Группа кнопок с слева от поля поиска  --}}
        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
            <div class="btn-group btn-market" role="group">

                {{-- Фильтр "Категории"--}}
                <button
                    type="button"
                    class="btn btn-outline-secondary dropdown-toggle s-control-gx rounded-start-0 "
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    Категории
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                    <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                    <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                </ul>
            </div>

            {{-- Опцыогально при клике отправить запрос поиска --}}
            <button type="button" class="btn btn-outline-secondary s-control-gx rounded-end-pill">Submit</button>
        </div>
    </div>
</div>












{{-- Оригинал
<div class="position-relative flex-fill d-none d-lg-block pe-4 pe-xl-5">
    <i class="ci-search position-absolute top-50 translate-middle-y d-flex fs-lg text-white ms-3"></i>
    <input type="search" class="form-control form-control-lg form-icon-start border-white rounded-pill" placeholder="Search the products">
</div>
--}}
