import type { Collection } from "@/types";
import type { Song } from "@/types/karaoke";
import { customFetch } from "../customFetch";

type GetCollectionParams = {
    pageParam?: number;
    search?: string;
    ready?: boolean;
    hasVocals?: boolean | null;
    format?: string[];
};

export async function getSongCollection({ pageParam = 1, search, ready, hasVocals, format }: GetCollectionParams): Promise<Collection<Song>> {
    const params = new URLSearchParams({
        page: String(pageParam),
        ready: ready ? 'true' : 'false',
    });

    if (search) {
        params.set('search', search);
    }

    if (hasVocals !== null && hasVocals !== undefined) {
        params.set('hasVocals', hasVocals ? 'true' : 'false');
    }

    if (format && format.length > 0) {
        format.forEach((f) => params.append('format[]', f));
    }

    const response = await customFetch(`/api/songs?${new URLSearchParams(params)}`, { method: 'GET' });

    return await response.json();
}