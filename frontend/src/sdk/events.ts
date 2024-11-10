import { PnEvent, PnListEvent } from "./responses/event";
import { Collection } from "./responses/collection";
import PnPicture from "./responses/picture";
import { PnSongSession } from "./responses/song";
import { SDK } from ".";

export class Events {
    private sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async getCollection(page: number, name: string|null = null, mine: boolean = true): Promise<Collection<PnListEvent> | null> {
        const searchParams = new URLSearchParams();
        searchParams.set('page', `${page}`);

        if (name) {
            searchParams.set('name', name);
        }

        if (mine) {
            searchParams.set('mine', 'true');
        }

        const resp = await this.sdk.get(`/api/events?${searchParams.toString()}`);
        const data = await resp.json();

        return Collection.fromJson<PnListEvent>(
            data,
            x => PnListEvent.fromJson(x),
        );
    }

    async get(id: string): Promise<PnEvent | null> {
        const resp = await this.sdk.get(`/api/events/${id}`);
        const data = await resp.json();

        return PnEvent.fromJson(data);
    }

    async getPictures(eventId: string, unattended: boolean = false): Promise<Collection<PnPicture> | null> {
        const resp = await this.sdk.get(`/api/events/${eventId}/pictures?unattended=${unattended}`);
        const data = await resp.json();

        return Collection.fromJson<PnPicture>(
            data,
            x => PnPicture.fromJson(x),
        );
    }

    async create(event: PnEvent): Promise<PnEvent|null> {
        const body: any = {
            'name': event.name,
            'author': event.author,
            'datetime': event.datetime.toISOString(),
            'location': event.location,
        };

        const resp = await this.sdk.post('/api/events', body);
        const data = await resp.json();

        return PnEvent.fromJson(data);
    }

    async update(event: PnEvent): Promise<PnEvent|null> {
        const body: any = {
            'name': event.name,
            'author': event.author,
            'datetime': event.datetime.toISOString(),
            'location': event.location,
        };

        const resp = await this.sdk.patch(`/api/events/${event.id}`, body);
        const data = await resp.json();

        return PnEvent.fromJson(data);
    }

    async upsert(event: PnEvent): Promise<PnEvent|null> {
        if (event.id) {
            return this.update(event);
        }

        return this.create(event);
    }

    async updateParticipants(event: PnEvent, participants: string[]): Promise<PnEvent|null> {
        const resp = await this.sdk.patch(`/api/events/${event.id}`, {
            'participants': participants,
        });

        const data = await resp.json();

        return PnEvent.fromJson(data);
    }

    async conclude(event: PnEvent): Promise<PnEvent|null> {
        const resp = await this.sdk.post(`/api/events/${event.id}/conclude`, {});
        const data = await resp.json();

        return PnEvent.fromJson(data);
    }

    async getSongSessions(event: PnEvent, page: number): Promise<Collection<PnSongSession>|null> {
        const resp = await this.sdk.get(`/api/events/${event.id}/song-sessions?page=${page}`);
        const data = await resp.json();

        return Collection.fromJson(data, x => PnSongSession.fromJson(x));
    }
}