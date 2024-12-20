import PnAppliance from './appliance';
import dayjs from 'dayjs';

export class User {
    iri: string;
    id: number;
    username: string;
    email: string;
    language: string;
    appliances: PnAppliance[];

    constructor(data: Record<string, any>) {
        this.iri = data['@id'];
        this.id = data['id'];
        this.username = data['username'];
        this.email = data['email'];
        this.language = data['language'];
        this.appliances = PnAppliance.fromArray(data['appliances']);
    }

    static fromJson(data: Record<string, any> | null) {
        if (!data) {
            return null;
        }

        return new User(data);
    }

    static fromArray(arr: Record<string, any>[]) {
        const users: User[] = [];

        arr.forEach((x) => {
            const user = User.fromJson(x);
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
    bannedAt: dayjs.Dayjs | null;

    constructor(data: Record<string, any>) {
        this.iri = data['@id'];
        this.id = data['id'];
        this.username = data['username'];
        this.email = data['email'];
        this.bannedAt = data['bannedAt'] ? dayjs(data['bannedAt']) : null;
    }

    static fromJson(data: Record<string, any> | null): PnListUser | null {
        if (!data) {
            return null;
        }

        return new PnListUser(data);
    }
}
