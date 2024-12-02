import './assets/css/index.scss';

import { ConfigProvider, theme } from 'antd';
import { I18nextProvider, initReactI18next } from 'react-i18next';
import { RouterProvider, createBrowserRouter, redirect } from 'react-router-dom';
import AdminBackdropsPage from './pages/admin/backdrops.tsx';
import AdminLayout from './layout/AdminLayout.tsx';
import AdminNewUserPage from './pages/admin/new_user.tsx';
import AdminUsersPage from './pages/admin/users.tsx';
import AuthProvider from './hooks/auth.tsx';
import AuthenticatedLayout from './layout/AuthenticatedLayout.tsx';
import Backend from 'i18next-http-backend';
import EditAppliancePage from './pages/appliances/EditAppliance.tsx';
import EditEventPage from './pages/EditEvent.tsx';
import EditSongPage from './pages/karaoke/EditSong.tsx';
import EventsPage from './pages/Events.tsx';
import LoginPage from './pages/login/index.tsx';
import MagicLoginPage from './pages/login/MagicLoginCallback.tsx';
import MyAccountPage from './pages/MyAccount.tsx';
import NewAppliancePage from './pages/appliances/NewAppliance.tsx';
import NewEventPage from './pages/NewEvent.tsx';
import NewSongPage from './pages/karaoke/NewSong.tsx';
import RequestSong from './pages/karaoke/RequestSong.tsx';
import ShowEventPage from './pages/ShowEvent.tsx';
import SongListingPage from './pages/karaoke/SongListing.tsx';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import detector from "i18next-browser-languagedetector";
import i18n from "i18next";
import AdminIndexPage from './pages/admin/index.tsx';

i18n
    .use(Backend)
    .use(detector)
    .use(initReactI18next)
    .init({
        fallbackLng: 'en',
        interpolation: {
            escapeValue: false,
        }
    });

const router = createBrowserRouter([
    {
        path: '/',
        loader: () => redirect('/events'),
    },
    {
        path: '/login',
        element: <LoginPage />,
    },
    {
        path: '/magic-login',
        element: <MagicLoginPage />,
    },
    {
        path: '/',
        element: <AuthenticatedLayout />,
        children: [
            {
                path: '/admin',
                element: <AdminLayout />,
                children: [
                    {
                        path: '',
                        element: <AdminIndexPage />
                    },
                    {
                        path: '/admin/users/new',
                        element: <AdminNewUserPage />
                    },
                    {
                        path: '/admin/users',
                        element: <AdminUsersPage />
                    },
                    {
                        path: '/admin/backdrops',
                        element: <AdminBackdropsPage />
                    },
                ],
            },
            {
                path: '/events',
                element: <EventsPage />,
            },
            {
                path: '/events/new',
                element: <NewEventPage />,
            },
            {
                path: '/events/:id',
                element: <ShowEventPage />,
            },
            {
                path: '/events/:id/edit',
                element: <EditEventPage />,
            },
            {
                path: '/karaoke',
                element: <SongListingPage />,
            },
            {
                path: '/karaoke/request',
                element: <RequestSong />,
            },
            {
                path: '/karaoke/new',
                element: <NewSongPage />,
            },
            {
                path: '/karaoke/:id',
                element: <EditSongPage />,
            },
            {
                path: '/me',
                element: <MyAccountPage />,
            },
            {
                path: '/appliances/new',
                element: <NewAppliancePage />,
            },
            {
                path: '/appliances/:id',
                element: <EditAppliancePage />,
            },
        ],
    },
]);

const phTheme = {
    token: {
        colorBgBase: '#262335',
        colorTextBase: '#8a8692',
        colorError: '#db3e4b',
        colorSuccess: '#5db793',
        colorPrimary: '#f92aa9',
        colorInfo: '#f92aa9',
        sizeStep: 4,
        sizeUnit: 4,
        borderRadius: 3,
        colorBgContainer: '#241b2f',
        colorBgElevated: '#2a2139',
        fontSize: 16,
    },
    components: {
        Typography: {
            algorithm: true,
        },
        Layout: {
            headerBg: 'rgb(23,21,32)',
            headerColor: 'rgb(211,208,212)',
            siderBg: 'rgb(23,21,32)',
        },
        Menu: {
            darkItemBg: 'rgb(23,21,32)',
        },
        Modal: {
            contentBg: 'rgb(23,21,32)',
        }
    },
    algorithm: [theme.darkAlgorithm, theme.compactAlgorithm],
};

createRoot(document.getElementById('root')!).render(
    <StrictMode>
        <I18nextProvider i18n={i18n}>
            <ConfigProvider theme={phTheme}>
                <AuthProvider>
                    <RouterProvider router={router} />
                </AuthProvider>
            </ConfigProvider>
        </I18nextProvider>
    </StrictMode>
);
