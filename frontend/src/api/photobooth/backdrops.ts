import type { Collection } from "@/types";
import { customFetch } from "../customFetch";
import type { BackdropAlbum } from "@/types/backdrops";

type Props = {
  pageParam?: number;
};

export async function getBackdropAlbums({ pageParam }: Props): Promise<Collection<BackdropAlbum>> {
  const resp = await customFetch(`/api/backdrop_albums?page=${pageParam ?? 1}`);

  return await resp.json();
}

export async function getBackdropAlbum(id: number | string): Promise<BackdropAlbum> {
  const resp = await customFetch(`/api/backdrop_albums/${id}`);

  return await resp.json();
}

export type UpsertBackdropAlbum = Omit<BackdropAlbum, '@id' | 'id' | 'backdrops'>;

export async function createBackdropAlbum(album: UpsertBackdropAlbum): Promise<BackdropAlbum> {
  const resp = await customFetch('/api/backdrop_albums', {
    method: 'POST',
    body: JSON.stringify(album),
  });

  return await resp.json();
}

export async function updateBackdropAlbum(id: number | string, album: UpsertBackdropAlbum): Promise<BackdropAlbum> {
  const resp = await customFetch(`/api/backdrop_albums/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(album),
  });

  return await resp.json();
};

export async function deleteBackdropAlbum(id: number | string): Promise<void> {
  await customFetch(`/api/backdrop_albums/${id}`, {
    method: 'DELETE',
  });
}

export async function deleteBackdrop(albumId: number|string, id: number | string): Promise<void> {
  await customFetch(`/api/backdrop_albums/${albumId}/backdrops/${id}`, {
    method: 'DELETE',
  });
}