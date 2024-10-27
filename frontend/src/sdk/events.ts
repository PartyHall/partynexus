import { PnEvent, PnListEvent } from "./responses/event";
import { Collection } from "./responses/collection";
import PnPicture from "./responses/picture";
import { SDK } from ".";

export class Events {
    private sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async getCollection(page: number, owner: string): Promise<Collection<PnListEvent> | null> {
        const resp = await this.sdk.get(`/api/events?page=${page}&owner=${owner}`);
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
}