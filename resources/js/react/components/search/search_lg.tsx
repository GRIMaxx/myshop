
// Подключить стили
import '@react_components/search/css/search.css'




export default function search_lg(props = {}) {

    console.log('search_lg', props);

    return (<h1>Test React</h1>);

};

/****
<div className="position-relative flex-fill d-none d-lg-block pe-1 pe-xl-2">
    <div className="d-flex">

        {{-- Поле поиска --}}
        <div className="flex-grow-1">
            <div className="input-group">
                <i className="ci-search position-absolute top-50 start-0 translate-middle-y fs-lg text-white ms-3"></i>
                <input
                    id="input_lg"
                    type="text"
                    className="form-control form-control-lg form-icon-start form-icon-end rounded-end-0 rounded-start-gx border-end-0 s-control-gx"
                    placeholder="Dropdown on the right"
                    aria-label="Text input with dropdown addon"
                    aria-describedby="dropdown-addon-right"
                />
                <i className="ci-external-link position-absolute top-50 end-0 translate-middle-y fs-lg text-white me-3"></i>
            </div>

            {{-- Блок результатов поиска (пока статичный, всегда виден) --}}
            <ul
                id="search-results"
                className="list-group position-absolute w-100 mt-1 shadow-sm search-panel list-group search-results"
                style="z-index: 1050;"
            >
                <li className="list-group-item">
                    <!-- Левая часть: картинка -->
                    <div className="me-3 flex-shrink-0 search-icon-wrapper">
                        <img src="assets/img/home/electronics/banner/camera.png" alt="icon" className="search-thumb"/>
                        <!-- Вариант с иконкой (через <use>) -->
                        <
                        !-- <svg className="search-thumb">
                        <use xlink:href="#mdi--plus-one"></use>
                    </svg> -->
                    </div>

                    <!-- Правая часть: контент -->
                    <div className="flex-grow-1">
                        <!-- Верхняя строка (иконки, бейджи и т.п.) -->
                        <div className="d-flex align-items-center small text-muted mb-1">
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <i className="ci-star me-2 text-warning"></i>
                            <i className="ci-heart me-2 text-danger"></i>
                            <i className="ci-tag me-2"></i>
                            <span className="badge bg-secondary me-2">Категория</span>
                            <span className="badge bg-info me-2">New</span>
                        </div>

                        <!-- Заголовок -->
                        <div className="fw-semibold text-truncate mb-1">
                            Название товара или длинный заголовок, который обрежется 40px
                        </div>

                        <!-- Дополнительный текст -->
                        <div className="text-muted small">
                            Здесь может быть описание товара, категория или что-то ещё.
                        </div>
                    </div>
                </li>

            </ul>
        </div>

        {{-- Группа кнопок с слева от поля поиска  --}}
        <div className="btn-group" role="group" aria-label="Button group with nested dropdown">
            <div className="btn-group btn-market" role="group">

                {{-- Фильтр "Категории"--}}
                <button
                    type="button"
                    className="btn btn-outline-secondary dropdown-toggle s-control-gx rounded-start-0 "
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    Категории
                </button>
                <ul className="dropdown-menu">
                    <li><a className="dropdown-item" href="#">Dropdown link</a></li>
                    <li><a className="dropdown-item" href="#">Dropdown link</a></li>
                    <li><a className="dropdown-item" href="#">Dropdown link</a></li>
                </ul>
            </div>

            {{-- Опцыогально при клике отправить запрос поиска --}}
            <button type="button" className="btn btn-outline-secondary s-control-gx rounded-end-pill">Submit</button>
        </div>
    </div>
</div>**/
