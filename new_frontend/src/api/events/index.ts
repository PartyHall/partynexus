import type { Collection } from "@/types";
import { customFetch } from "../customFetch";
import type { EventListItem, Event } from "@/types/event";

type GetAllEventsParams = {
    pageParam?: number;
    mineOnly?: boolean;
};

export async function getAllEvents({ pageParam = 0, mineOnly }: GetAllEventsParams): Promise<Collection<EventListItem>> {
    const qp = new URLSearchParams();
    qp.set('page', pageParam.toString());
    qp.set('mine', mineOnly ? 'true' : 'false');

    const resp = await customFetch('/api/events?' + qp.toString());

    return await resp.json();
}

export async function getEventById(id: string): Promise<Event> {
    const resp = await customFetch(`/api/events/${id}`);

    return await resp.json();
}