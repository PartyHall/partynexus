import { PnListUser, User, UserAuthenticationLog } from './responses/user';
import { Collection } from './responses/collection';
import { SDK } from '.';

export default class Users {
    sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async getCollection(
        query: string | null = null,
        page: number = 1,
        showBanned: boolean = false
    ): Promise<Collection<PnListUser> | null> {
        const searchParams = new URLSearchParams({
            page: `${page}`,
        });

        if (!!query && query.trim().length > 0) {
            searchParams.set('username', query);
        }

        if (showBanned) {
            searchParams.set('showBanned', `${showBanned}`);
        }

        const resp = await this.sdk.get(
            `/api/users?${searchParams.toString()}`
        );
        const data = await resp.json();

        return Collection.fromJson(data, (x) => PnListUser.fromJson(x));
    }

    async getFromIri(iri: string): Promise<User | null> {
        const resp = await this.sdk.get(iri);
        const data = await resp.json();

        return User.fromJson(data);
    }

    async register(
        username: string,
        email: string,
        language: string
    ): Promise<User | null> {
        const resp = await this.sdk.post('/api/users', {
            username,
            email,
            language,
        });
        const data = await resp.json();

        return User.fromJson(data);
    }

    async ban(iri: string): Promise<PnListUser | null> {
        const resp = await this.sdk.post(iri + '/ban', []);
        const data = await resp.json();

        return PnListUser.fromJson(data);
    }

    async unban(iri: string): Promise<PnListUser | null> {
        const resp = await this.sdk.post(iri + '/unban', []);
        const data = await resp.json();

        return PnListUser.fromJson(data);
    }

    async update(id: number, postData: User): Promise<User | null> {
        const resp = await this.sdk.patch(`/api/users/${id}`, postData);
        const data = await resp.json();

        return User.fromJson(data);
    }

    async getAuthenticationLogs(user: number, page: number = 1) {
        const resp = await this.sdk.get(
            `/api/users/${user}/auth-logs?page=${page}`
        );
        const data = await resp.json();

        return Collection.fromJson(data, (x) =>
            UserAuthenticationLog.fromJson(x)
        );
    }
}
