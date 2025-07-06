import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [tailwindcss(), react()],
    // Temp fix for tabler-icons
    // => https://github.com/tabler/tabler-icons/issues/1233#issuecomment-2428245119
    resolve: {
        alias: {
            // /esm/icons/index.mjs only exports the icons statically, so no separate chunks are created
            '@tabler/icons-react':
                '@tabler/icons-react/dist/esm/icons/index.mjs',
        },
    },
    optimizeDeps: {
        include: ['@tabler/icons-react'],
        entries: ['@tabler/icons-react/**/*.js'],
    },
    server: {
        proxy: {
            '/api': {
                target: 'http://app:80',
            },
            '/song_covers': {
                target: 'http://app:80',
            },
            '/.well-known': {
                target: 'http://app:80',
            },
        },
    },
});
