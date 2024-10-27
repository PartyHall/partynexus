import { EmbeddedUser } from "./responses/user";
import { SDK } from ".";

export default class Users {
    sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async search(query: string): Promise<EmbeddedUser[]> {
        const resp = await this.sdk.get(`/api/users?username=${query}`);
        const data = await resp.json();

        return EmbeddedUser.fromArray(data['member']);
    }
}