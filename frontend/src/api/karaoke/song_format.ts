import type { Collection, EnumValue } from "@/types";
import { customFetch } from "../customFetch";

export async function getSongFormats(): Promise<Collection<EnumValue>> {
  const response = await customFetch(`/api/song_formats`, { method: "GET" });

  return await response.json();
}
