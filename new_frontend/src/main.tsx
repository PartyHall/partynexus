import './assets/css/glow.css'
import './assets/css/index.css'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { routeTree } from './routeTree.gen';
import { createRouter, RouterProvider } from '@tanstack/react-router';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { I18nextProvider, initReactI18next } from 'react-i18next';
import Backend from 'i18next-http-backend';
import detector from 'i18next-browser-languagedetector';
import i18n from 'i18next';
import { closeSnackbar, SnackbarProvider } from 'notistack';
import Button from './components/generic/button';
import { IconX } from '@tabler/icons-react';

const router = createRouter({ routeTree });
const queryClient = new QueryClient();

declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router
  }
}

i18n.use(Backend)
  .use(detector)
  .use(initReactI18next)
  .init({
    fallbackLng: 'en',
    interpolation: {
      escapeValue: false,
    },
  });

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <I18nextProvider i18n={i18n}>
      <QueryClientProvider client={queryClient}>
        <RouterProvider
          router={router}
        />
        <SnackbarProvider
          anchorOrigin={{ horizontal: 'right', vertical: 'top' }}
          autoHideDuration={3000}
          action={(id) => <Button onClick={() => closeSnackbar(id)}><IconX /></Button>}
        />
      </QueryClientProvider>
    </I18nextProvider>
  </StrictMode>,
)
