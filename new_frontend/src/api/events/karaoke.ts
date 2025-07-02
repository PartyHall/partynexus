import type { Collection } from "@/types";
import { customFetch } from "../customFetch";
import type { SongSession } from "@/types/karaoke";

export async function getSongSessions(eventId: string): Promise<Collection<SongSession>> {
  const resp = await customFetch(`/api/events/${eventId}/song-sessions`);

  return await resp.json();
}