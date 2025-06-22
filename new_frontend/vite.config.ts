import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { tanstackRouter } from '@tanstack/router-plugin/vite';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
  plugins: [
    tailwindcss(),
    tanstackRouter({
      target: 'react',
      autoCodeSplitting: true,
    }),
    react(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
      '@tabler/icons-react': '@tabler/icons-react/dist/esm/icons/index.mjs',
    }
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
      '/.well-known/mercure': {
        target: 'http://app:80',
      },
    }
  }
})
