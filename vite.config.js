import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react'

import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            // Здесь точки входа для сборки например - CSS через Vite.
            // Vite будет компилировать SCSS в CSS и выдавать app.css.
            input: [
                // Этих точек достаточно для полной работы!
                'resources/js/app.js',              // Глобальная точка
                'resources/scss/app.scss',          // Подключаем SCSS (CSS, Переопределения Bootstrap, Кастомные компоненты, Глобальные стили приложения)
            ],
            refresh: true,
        }),
        react(),                                    //
    ],

    //
    resolve: {
        alias: {
            // чтобы можно было писать `@import "bootstrap/..."` из SCSS
            'bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),

            '@': path.resolve(__dirname, 'resources/js/'),
            '@libs': path.resolve(__dirname, 'resources/js/libs/'),
            '@react': path.resolve(__dirname, 'resources/js/react/'),
            '@react_utils': path.resolve(__dirname, 'resources/js/react/utils/'),
            '@react_components': path.resolve(__dirname, 'resources/js/react/components/'),
            '@icons': path.resolve(__dirname, 'resources/icons/'),
        },
    },

    // -- Продакшен
    build: {
        rollupOptions: {
            output: {
                // Ручное разделение кода
                // Создает отдельный файл vendor-[hash].js, куда помещает указанные библиотеки (react, react-dom,..).
                // Зачем нужно:
                // Кэширование: Браузер кэширует vendor.js отдельно от моего кода.
                // При обновлении приложения пользователи скачивают только изменившиеся файлы.
                // Оптимизация загрузки: Посетители, которые уже были на сайте, получают vendor.js из кэша.
                // Пример: Если у меня 10 страниц, то react загрузится один раз, а не 10 раз.
                // Это гарантирует явное распределение: react/bootstrap/... попадут в свои
                // vendor-чанки и будут переиспользованы.
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('react')) return 'vendor_react'
                        if (id.includes('bootstrap')) return 'vendor_bootstrap'
                        if (id.includes('jquery')) return 'vendor_jquery'

                        //if (id.includes('floating-ui')) return 'vendor_floating_ui'
                        if (id.includes('@popperjs/core')) return 'vendor_popperjs_core'
                        //if (id.includes('intl-tel-input')) return 'vendor_intl_tel_input'
                        if (id.includes('@fingerprintjs/fingerprintjs')) return 'vendor_fingerprintjs_fingerprintjs'

                        return 'vendor_misc'
                    }
                }
            }
       }
    },

    // -- Настройки связи с сервером + сертификат
    server: {
        host: '0.0.0.0',
        port: 5173,
        https: {
            key: fs.readFileSync('/home/gxadm/certs/server.key'),
            cert: fs.readFileSync('/home/gxadm/certs/server.crt'),
        },
        hmr: {
            host: 'myshop.local',   // https://myshop.local/
            port: 5173,             // Для Vite
            protocol: 'wss',
        },
        cors: true,
        watch: {
            usePolling: true,
        },
    },
});
