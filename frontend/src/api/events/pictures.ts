import type { Collection } from "@/types";
import { customFetch } from "../customFetch";
import type { Picture } from "@/types/photo";

export async function getPicturesForEvent(
  eventId: string,
  showUnattended: boolean | null = null,
): Promise<Collection<Picture>> {
  const qs = new URLSearchParams();

  if (showUnattended !== null) {
    qs.append("unattended", showUnattended ? "true" : "false");
  }

  const resp = await customFetch(`/api/events/${eventId}/pictures`);

  return await resp.json();
}
