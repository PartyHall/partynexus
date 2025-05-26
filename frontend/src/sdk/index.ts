import ValidationError, {
    ValidationErrors,
} from './responses/validation_error';
import { Appliances } from './appliances';
import Auth from './auth';
import { Backdrops } from './backdrops';
import { Events } from './events';
import Karaoke from './karaoke';
import { PnTokenUser } from './responses/auth';
import { SdkError } from './responses/error';
import Users from './users';

import dayjs from 'dayjs';
import DisplayBoards from './display_boards';

export type StoreToken = (
    token: string | null,
    refreshToken: string | null
) => void;

export type OnExpired = () => void;

export class SDK {
    public token: string | null;
    public refreshToken: string | null;

    private autorefreshTimeout: number | null = null;
    private storeToken: StoreToken = () => {};
    private onExpired: OnExpired = () => {};

    public tokenUser: PnTokenUser | null = null;
    private baseUrl: string;

    public auth: Auth;
    public events: Events;
    public karaoke: Karaoke;
    public users: Users;
    public appliances: Appliances;
    public backdrops: Backdrops;
    public displayBoards: DisplayBoards;

    public constructor(
        baseUrl: string,
        token: string | null,
        refreshToken: string | null,
        storeToken?: StoreToken,
        onExpired?: OnExpired
    ) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
        this.token = token;
        this.refreshToken = refreshToken;

        this.auth = new Auth(this);
        this.events = new Events(this);
        this.karaoke = new Karaoke(this);
        this.users = new Users(this);
        this.appliances = new Appliances(this);
        this.backdrops = new Backdrops(this);
        this.displayBoards = new DisplayBoards(this);

        this.storeToken = storeToken || (() => {});
        this.setOnExpired(onExpired);

        if (token) {
            this.setToken(token, refreshToken);
        }
    }

    async request(url: string, init?: RequestInit) {
        if (url.startsWith('/')) {
            url = this.baseUrl + url;
        }

        if (!init) {
            init = {
                headers: {},
            };
        } else if (!init.headers) {
            init.headers = {};
        }

        if (this.token) {
            init.headers = {
                ...init.headers,
                Authorization: 'Bearer ' + this.token,
            };
        }

        try {
            const resp = await fetch(url, init);

            if (this.isHttpError(resp)) {
                let body: any = await resp.text();
                try {
                    body = JSON.parse(body);
                    if (body.message) {
                        body = body.message;
                    }
                } catch {
                    /* empty */
                }

                throw new SdkError(resp.status, body);
            }

            return resp;
        } catch (e: any) {
            if (this.onExpired && e.status == 401) {
                this.onExpired();
            }

            if (e.status === 422) {
                throw await this.parseValidationErrors(e.message);
            }

            throw e;
        }
    }

    async get(url: string, options?: any) {
        return await this.request(url, options);
    }

    async post(url: string, data?: any, options?: any) {
        if (!options) {
            options = {};
        }

        if (data) {
            if (!options.headers) {
                options.headers = {};
            }

            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }

        options['method'] = 'POST';
        return await this.request(url, options);
    }

    async patch(url: string, data?: any, options?: any) {
        if (!options) {
            options = { headers: {} };
        }

        if (data) {
            if (!options.headers) {
                options.headers = {};
            }

            options.headers['Content-Type'] = 'application/merge-patch+json';
            options.body = JSON.stringify(data);
        }

        options['method'] = 'PATCH';
        return await this.request(url, options);
    }

    async put(url: string, data?: any, options?: any) {
        if (!options) {
            options = { headers: {} };
        }

        if (data) {
            if (!options.headers) {
                options.headers = {};
            }

            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }

        options['method'] = 'PUT';
        return await this.request(url, options);
    }

    async delete(url: string) {
        return await this.request(url, { method: 'DELETE' });
    }

    async parseValidationErrors(data: any) {
        if (data['@type'] !== 'ConstraintViolationList') {
            return data;
        }

        const errors: any = {};

        data.violations.forEach((x: any) => {
            if (!Object.keys(errors).includes(x.propertyPath)) {
                errors[x.propertyPath] = new ValidationError(
                    x.propertyPath,
                    []
                );
            }

            errors[x.propertyPath].errors.push(x.message);
        });

        return new ValidationErrors(Object.values(errors));
    }

    isHttpError(response: Response): boolean {
        return !(response.status >= 200 && response.status <= 299);
    }

    setToken(token: string | null, refreshToken: string | null) {
        this.clearRefresh();

        this.token = token;
        this.refreshToken = refreshToken;
        this.storeToken(token, refreshToken);

        this.tokenUser = PnTokenUser.fromToken(token);

        this.autoRefresh();
    }

    setStoreToken(
        storeToken: (token: string | null, refreshToken: string | null) => void
    ) {
        this.storeToken = storeToken;
    }

    setOnExpired(onExpired?: OnExpired) {
        this.onExpired = onExpired ?? (() => {});
    }

    clearRefresh() {
        if (this.autorefreshTimeout !== null) {
            clearTimeout(this.autorefreshTimeout);
        }
    }

    private async autoRefresh() {
        if (!this.refreshToken || !this.tokenUser) {
            return;
        }

        const diffSeconds = this.tokenUser.expiresAt.diff(dayjs(), 'seconds');

        if (diffSeconds < 30) {
            try {
                const data = await this.auth.refresh(this.refreshToken);
                this.setToken(data.token, data.refresh_token);
            } catch {
                this.setToken(null, null);
            }
        } else {
            setTimeout(() => this.autoRefresh(), (diffSeconds - 30) * 1000);
        }
    }
}
