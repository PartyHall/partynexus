import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [react()],
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
