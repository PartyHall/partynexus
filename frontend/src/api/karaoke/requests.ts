import type { SongRequest } from "@/types/karaoke";
import { customFetch } from "../customFetch";
import type { Collection } from "@/types";

export async function getSongRequests(
  pageParam: number = 1,
): Promise<Collection<SongRequest>> {
  const response = await customFetch(`/api/song_requests?page=${pageParam}`, {
    method: "GET",
  });

  return await response.json();
}

export async function createSongRequest(
  title: string,
  artist: string,
): Promise<SongRequest> {
  const response = await customFetch("/api/song_requests", {
    method: "POST",
    body: JSON.stringify({ title, artist }),
  });

  return await response.json();
}

export async function deleteSongRequest(id: number): Promise<void> {
  await customFetch(`/api/song_requests/${id}`, { method: "DELETE" });
}
