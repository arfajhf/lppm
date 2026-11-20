import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // Preserve the browser targets used before the Vite security upgrade.
    build: {
        target: ['es2020', 'edge88', 'firefox78', 'chrome87', 'safari14'],
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
