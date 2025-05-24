import DisplayBoardKey from "./responses/display_board_key";
import { SDK } from ".";

export default class DisplayBoards {
    private sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async create(event: string): Promise<DisplayBoardKey | null> {
        const resp = await this.sdk.post(`/api/display_board_keys`, { 
            event: `/api/events/${event}`,
        });

        return DisplayBoardKey.fromJson(await resp.json());
    }
}