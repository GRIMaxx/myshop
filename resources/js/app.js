
// SCSS импорт делай в app.scss, а JS здесь
import '../scss/app.scss'

// Импорт глобальных библиотек
import './libs/index';                              // jQuery, ...., или bootstrap но одной строкой

// Модульные импорты конкретных компонентов bootstrap
import './libs/bootstrap';                          // bootstrap JS (модули)

// Подключаем компоненты React
import { components } from './react/index';         // Импортируем компоненты для дальнейшего использования

// Инициализация React с компонентами
import { createRoot } from 'react-dom/client';      // Подключаем React пакет для внедрения в HTML

// Подключаем кастомные скрипты из index.js
import './customs/index';                           // Здесь будет подключение всех кастомных скриптов





//document.querySelectorAll('[data-component]').forEach(el => {
    //const name = el.dataset.component;
    //const Component = components[name];
    //if (!Component) return;
    //let props = {};
    //if (el.dataset.props) {
    //    try { props = JSON.parse(el.dataset.props); } catch (e) { console.error(e); }
    //}
    //createRoot(el).render(<Component {...props} />);
//});

//===================================

//resources/js/
//    app.js            <-- главный entry
//    libs/
//        index.js        <-- jQuery, axios, global libraries
//        axiosConfig.ts
//    bootstrap/
//        core.js         <-- bootstrap js по модулям
//
//
//    resources/
//      scss/
//          _variables.scss
//          components/
//              _bootstrap-overrides.scss
//          app.scss        // точка входа для css
//      js/
//          app.js          // глобальный entry: общие скрипты, window.* assignment
//          vendor.js       // (опционально) дополнительные глобальные библиотеки
//          react/
//              index.tsx     // SPA/часть react (если есть)
//              product.tsx   // React widget для карточки товара (lazy load)react/

//resources/
//├─ js/
//│  ├─ app.js                # главный entry: глобальные библиотеки + React init
//│  ├─ libs/                 # глобальные библиотеки
//│  │  ├─ index.js           # jQuery, axios, simplebar и т.д.
//│  │  └─ bootstrap.js       # bootstrap JS по модулю
//│  ├─ react/                # React компоненты
//│  │  ├─ index.tsx          # экспорт {components} всех React компонентов
//│  │  ├─ search.tsx
//│  │  ├─ product.tsx
//│  │  └─ cart.tsx
//│  └─ utils/                # вспомогательные JS/TS модули
//├─ scss/
//│  ├─ app.scss              # главный entry для SCSS
//│  └─ components/           # переопределения bootstrap, кастомные стили




//TypeScript / React — какие расширения и почему

//    .ts — чистый TypeScript, без JSX. Используй для утилитарных модулей, сервисов, типов.
//    TypeScript Course

//    .tsx — TypeScript + JSX. Используй для React-компонентов (если проект на TypeScript). Если файл содержит JSX — должен быть .tsx.
//    Stack Overflow
//+1

//    .js — обычный JS (если не используешь TypeScript).

//.jsx — JS + JSX (React без TypeScript).
//Правило: если файл содержит JSX/TSX — давай ему соответствующее расширение (.jsx/.tsx) — многие инструменты (включая Vite) полагаются на это.
//    Reddit

// ====================================


