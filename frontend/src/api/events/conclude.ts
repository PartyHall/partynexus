import type { Event } from "@/types/event";
import { customFetch } from "../customFetch";

export default async function concludeEvent(eventId: string): Promise<Event> {
  const resp = await customFetch(`/api/events/${eventId}/conclude`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: "{}",
  });

  return await resp.json();
}
