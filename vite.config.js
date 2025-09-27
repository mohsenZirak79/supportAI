// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';
// import tailwindcss from '@tailwindcss/vite';
//
// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//         tailwindcss(),
//     ],
// });



const isDocker = process.env.DOCKER === 'true';

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/auth.css',
                'resources/css/user.css',
                'resources/js/app.js',
                'resources/js/chat.js',
                'resources/js/ticket.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    // server: {
    //     host: '0.0.0.0', // برای دسترسی از هاست
    //     port: 5173,
    //     hmr: isDocker
    //         ? {
    //             host: 'localhost',
    //             protocol: 'ws',
    //             clientPort: 5173,
    //         }
    //         : true, // پیش‌فرض
    // },
    build: {
        outDir: 'public/build',
        assetsDir: 'assets',
        manifest: true,
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('auth')) return 'auth';
                    if (id.includes('chat')) return 'chat';
                    if (id.includes('ticket')) return 'ticket';
                    return 'app'; // بقیه تو app می‌رن
                },
            },
        },
    },
});
