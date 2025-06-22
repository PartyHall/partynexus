import type { Collection } from "@/types";
import type { Song } from "@/types/karaoke";
import { customFetch } from "../customFetch";

type GetCollectionParams = {
    pageParam?: number;
    search?: string;
    ready?: boolean;
};

export async function getSongCollection({ pageParam = 1, search, ready }: GetCollectionParams): Promise<Collection<Song>> {
    const params: Record<string, string> = {
        page: String(pageParam),
        ready: ready ? 'true' : 'false',
    };

    if (search) {
        params.search = search;
    }

    const response = await customFetch(`/api/songs?${new URLSearchParams(params)}`, { method: 'GET' });

    return await response.json();
}