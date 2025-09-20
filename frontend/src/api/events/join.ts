import type { Event } from "@/types/event";
import { customFetch } from "../customFetch";

export async function joinEvent(registrationCode: string): Promise<Event> {
    const resp = await customFetch(`/api/join_event/${registrationCode}`, {
        method: "POST",
        body: JSON.stringify({}), // Important for API Platform to not yell at us
    });

    return await resp.json();
}