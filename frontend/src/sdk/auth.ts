import { AuthResponse } from "./responses/auth";
import { SDK } from ".";

export default class Auth {
    private sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async login(username: string, password: string): Promise<AuthResponse> {
        const data = await this.sdk.post(
            '/api/login',
            {
                'username': username,
                'password': password,
            },
        );

        return await data.json();
    }

    async magicLoginRequest(email: string): Promise<Response> {
        return await this.sdk.post(
            '/api/magic-login',
            { 'email': email },
        );
    }

    async magicLogin(email: string, code: string): Promise<AuthResponse> {
        try {
            const data = await this.sdk.post(
                '/api/magic-login-callback',
                {
                    'email': email,
                    'code': code,
                },
            );
    
            return await data.json();
        } catch (e) {
            if ((e as any).status === 409) {
                throw 'login.magic_login_callback.fail.desc_already_used';
            }

            if ((e as any).status === 410) {
                throw 'login.magic_login_callback.fail.desc_expired';
            }

            console.error(e);
            throw 'login.magic_login_callback.fail.desc_unknown';
        }
    }

    async refresh(refreshToken: string): Promise<AuthResponse> {
        const data = await this.sdk.post(
            '/api/refresh',
            {
                'refresh_token': refreshToken,
            },
        );

        return await data.json();
    }
}