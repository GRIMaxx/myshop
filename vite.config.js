import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        https: {
            key: fs.readFileSync('/home/gxadm/certs/server.key'),
            cert: fs.readFileSync('/home/gxadm/certs/server.crt'),
        },
        hmr: {
            host: 'myshop.local',
            protocol: 'wss',
        },
        cors: true,
        watch: {
            usePolling: true,
        },
    },
});
