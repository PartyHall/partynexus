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