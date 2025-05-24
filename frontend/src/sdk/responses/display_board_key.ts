export default class DisplayBoardKey {
    id: number;
    key: string;
    event: string;
    url: string;

    constructor(data: Record<string, any>) {
        this.id = data.id;
        this.key = data.key;
        this.event = data.event;
        this.url = data.url;
    }

    public static fromJson(data: Record<string, any>|null): DisplayBoardKey|null {
        if (!data) {
            return null;
        }

        return new DisplayBoardKey(data);
    }
}