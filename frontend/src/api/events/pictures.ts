import type { Collection } from "@/types";
import { customFetch } from "../customFetch";
import type { Picture } from "@/types/photo";

export async function getPicturesForEvent(eventId: string): Promise<Collection<Picture>> {
    const resp = await customFetch(`/api/events/${eventId}/pictures`);

    return await resp.json();
}