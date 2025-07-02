import type { Export } from "./export";
import type { DisplayBoardKey } from "./photo";
import type { MinimalUser } from "./user";

export type EventListItem = {
    '@id': string;
    '@type': string;

    id: string;
    name: string;
    owner: MinimalUser;
    datetime: string;
    location?: string|null;
    author?: string|null;
}

export type Event = EventListItem & {
    over: boolean;
    export: Export|null;
    participants: MinimalUser[];
    displayBoardKey: DisplayBoardKey|null;
};

export type UpsertEvent = {
    name: string;
    datetime: string;
    location?: string|null;
    author?: string|null;
};