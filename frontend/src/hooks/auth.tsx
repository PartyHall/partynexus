import {
    ReactNode,
    createContext,
    useCallback,
    useContext,
    useEffect,
    useState,
} from 'react';

import Cookies from 'js-cookie';
import { PnEvent } from '../sdk/responses/event';
import { SDK } from '../sdk';
import { User } from '../sdk/responses/user';

const BASE_URL = ''; // http://192.168.14.10:5175';

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

    user: User | null;
};

type AuthContextProps = AuthProps & {
    login: (username: string, password: string) => Promise<void>;
    magicLogin: (email: string, code: string) => Promise<void>;
    setToken: (token: string, refresh: string) => void;
    isLoggedIn: () => boolean;
    isGranted: (role: string) => boolean;
    isAdminOrEventOwner: (event?: PnEvent | null) => boolean;
    refreshUser: () => Promise<void>;
    logout: () => void;
};

const defaultProps: AuthProps = {
    loaded: false,
    api: new SDK(BASE_URL, TOKEN, REFRESH_TOKEN, storeToken, () =>
        storeToken(null, null)
    ),
    user: null,
};

const AuthContext = createContext<AuthContextProps>({
    ...defaultProps,
    login: async () => {},
    magicLogin: async () => {},
    setToken: () => {},
    isGranted: () => false,
    isAdminOrEventOwner: () => false,
    isLoggedIn: () => false,
    refreshUser: async () => {},
    logout: () => {},
});

export default function AuthProvider({ children }: { children: ReactNode }) {
    const [context, setContext] = useState<AuthProps>(defaultProps);

    const login = async (username: string, password: string) => {
        const data = await context.api.auth.login(username, password);
        setToken(data.token, data.refresh_token);
    };

    const magicLogin = async (email: string, code: string) => {
        const data = await context.api.auth.magicLogin(email, code);
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
                    api: new SDK(BASE_URL, null, null, storeToken, () =>
                        storeToken(null, null)
                    ),
                }));

                return;
            }

            setContext((oldCtx) => ({
                ...oldCtx,
                loaded: true,
                api: new SDK(BASE_URL, token, refresh, storeToken, () =>
                    storeToken(null, null)
                ),
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
    };

    const isAdminOrEventOwner = (event?: PnEvent | null) => {
        if (isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (!event) {
            return false;
        }

        return event.owner.iri === context.api.tokenUser?.iri;
    };

    const refreshUser = async () => {
        if (!context.api.tokenUser) {
            return;
        }

        const user = await context.api.users.getFromIri(
            context.api.tokenUser.iri
        );

        setContext((x) => ({
            ...x,
            user,
        }));
    };

    useEffect(() => {
        // Tries to fix the weird issue
        // where going on the site when I've not been on it for a long time
        // freezes on the "LOADING" instead of
        // fetching / kicking out
        setToken(TOKEN ?? undefined, REFRESH_TOKEN ?? undefined);
    }, []);

    return (
        <AuthContext.Provider
            value={{
                ...context,
                login,
                magicLogin,
                setToken,
                isGranted,
                isAdminOrEventOwner,
                isLoggedIn,
                refreshUser,
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
