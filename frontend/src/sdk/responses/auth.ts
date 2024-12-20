import dayjs from 'dayjs';

export interface AuthResponse {
    token: string;
    refresh_token: string;
}

export class PnTokenUser {
    iri: string;
    username: string;
    expiresAt: dayjs.Dayjs;
    roles: string[];

    constructor(
        iri: string,
        username: string,
        expiresAt: dayjs.Dayjs,
        roles: string[]
    ) {
        this.iri = iri;
        this.expiresAt = expiresAt;
        this.username = username;
        this.roles = roles;
    }

    static fromToken(token: string | null) {
        if (!token) {
            return null;
        }

        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');

        const data = JSON.parse(
            decodeURIComponent(
                window
                    .atob(base64)
                    .split('')
                    .map(function (c) {
                        return (
                            '%' +
                            ('00' + c.charCodeAt(0).toString(16)).slice(-2)
                        );
                    })
                    .join('')
            )
        );

        return PnTokenUser.fromJson(data);
    }

    static fromJson(data: Record<string, any> | null) {
        if (!data) {
            return null;
        }

        return new PnTokenUser(
            data['iri'],
            data['username'],
            dayjs.unix(data['exp']),
            data['roles']
        );
    }
}
