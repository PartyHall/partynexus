import dayjs from "dayjs";

export class EmbeddedUser {
    iri: string;
    id: number;
    username: string;
    email: string;

    constructor(data: Record<string, any>) {
        this.iri = data['@id'];
        this.id = data['id'];
        this.username = data['username'];
        this.email = data['email'];
    }

    static fromJson(data: Record<string, any>|null) {
        if (!data) {
            return null;
        }

        return new EmbeddedUser(data);
    }

    static fromArray(arr: Record<string, any>[]) {
        const users: EmbeddedUser[] = [];

        arr.forEach(x => {
            const user = EmbeddedUser.fromJson(x);
            if (user) {
                users.push(user);
            }
        });

        return users;
    }
}

export class PnListUser {
    iri: string;
    id: number;
    username: string;
    email: string;
    bannedAt: dayjs.Dayjs|null;

    constructor(data: Record<string, any>) {
        this.iri = data['@id'];
        this.id = data['id'];
        this.username = data['username'];
        this.email = data['email'];
        this.bannedAt = data['bannedAt'] ? dayjs(data['bannedAt']) : null;
    }

    static fromJson(data: Record<string, any>|null): PnListUser|null
    {
        if (!data) {
            return null;
        }

        return new PnListUser(data);
    }
}