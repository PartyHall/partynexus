import { customFetch } from "../customFetch";
import type { Event } from "@/types/event";

export async function addParticipantToEvent(event: Event, userIri: string): Promise<Event | null> {
    if (event.participants.some((x) => x['@id'] === userIri)) {
        return event;
    }

    const resp = await customFetch(`/api/events/${event.id}`, {
        method: 'PATCH',
        body: JSON.stringify({
            participants: [...event.participants.map((x) => x['@id']), userIri],
        }),
    });

    return await resp.json();
}

export async function removeParticipantFromEvent(event: Event, userIri: string): Promise<Event | null> {
    const resp = await customFetch(`/api/events/${event.id}`, {
        method: 'PATCH',
        body: JSON.stringify({
            participants: event.participants
                .map((x) => x['@id'])
                .filter((x) => x !== userIri),
        }),
    });

    return await resp.json();
}