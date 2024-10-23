import { ReactNode, createContext, useCallback, useContext, useState } from 'react';

import Cookies from 'js-cookie';
import { SDK } from '../sdk';

const BASE_URL = import.meta.env.VITE_API_URL;

const TOKEN = localStorage.getItem('token');
const REFRESH_TOKEN = localStorage.getItem('refresh_token');

const storeToken = (token: string | null, refresh: string | null) => {
    if (!token || !refresh) {
        localStorage.removeItem('token');
        localStorage.removeItem('refresh_token');

        Cookies.remove('mercureAuthorization');
        Cookies.remove('partynexus_jwt');

        return;
    }

    localStorage.setItem('token', token);
    localStorage.setItem('refresh_token', refresh);

    Cookies.set('mercureAuthorization', token);
    Cookies.set('partynexus_jwt', token);
};

type AuthProps = {
    loaded: boolean;
    api: SDK;
};

type AuthContextProps = AuthProps & {
    login: (username: string, password: string) => Promise<void>;
    setToken: (token: string, refresh: string) => void;
    isLoggedIn: () => boolean;
    isGranted: (role: string) => boolean;
    logout: () => void;
};

const defaultProps: AuthProps = {
    loaded: false,
    api: new SDK(BASE_URL, TOKEN, REFRESH_TOKEN, storeToken),
};

const AuthContext = createContext<AuthContextProps>({
    ...defaultProps,
    login: async () => { },
    setToken: () => { },
    isGranted: () => false,
    isLoggedIn: () => false,
    logout: () => { },
});

export default function AuthProvider({ children }: { children: ReactNode }) {
    const [context, setContext] = useState<AuthProps>(defaultProps);

    const login = async (username: string, password: string) => {
        const data = await context.api.auth.login(username, password);
        setToken(data.token, data.refresh_token);
    };

    const setToken = useCallback(
        (token?: string, refresh?: string) => {
            if (!token || !refresh) {
                localStorage.removeItem('token');
                localStorage.removeItem('refresh_token');
                Cookies.remove('mercureAuthorization');
                Cookies.remove('partynexus_jwt');

                setContext((oldCtx) => ({
                    ...oldCtx,
                    loaded: true,
                    api: new SDK(BASE_URL, null, null, storeToken),
                }));

                return;
            }

            setContext((oldCtx) => ({
                ...oldCtx,
                loaded: true,
                api: new SDK(BASE_URL, token, refresh, storeToken),
            }));

            localStorage.setItem('token', token);
            localStorage.setItem('refresh_token', refresh);
            Cookies.set('mercureAuthorization', token);
            Cookies.set('partynexus_jwt', token);
        },
        [context]
    );

    const isLoggedIn = () => !!context.api.tokenUser;

    const logout = () => setToken();

    const isGranted = (role: string) => {
        if (!context.api.tokenUser) {
            return false;
        }

        return context.api.tokenUser.roles.includes(role);
    }

    return (
        <AuthContext.Provider
            value={{
                ...context,
                login,
                setToken,
                isGranted,
                isLoggedIn,
                logout,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    return useContext<AuthContextProps>(AuthContext);
}