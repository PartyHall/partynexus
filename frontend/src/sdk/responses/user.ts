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

    static fromUser(data: Record<string, any>|null) {
        if (!data) {
            return null;
        }

        return new EmbeddedUser(data);
    }

    static fromArray(arr: Record<string, any>[]) {
        const users: EmbeddedUser[] = [];

        arr.forEach(x => {
            const user = EmbeddedUser.fromUser(x);
            if (user) {
                users.push(user);
            }
        });

        return users;
    }
}