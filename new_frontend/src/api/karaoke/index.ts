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

export async function getSong(id: string | number): Promise<Song> {
    const resp = await customFetch(`/api/songs/${id}`);
    return await resp.json();
}

export async function createSong(song: Song): Promise<Song> {
    const resp = await customFetch('/api/songs', {
        method: 'POST',
        body: JSON.stringify({
            title: song.title,
            artist: song.artist,
            format: '/api/song_formats/' + song.format,
            quality: '/api/song_qualities/' + song.quality,
            musicBrainzId: song.musicBrainzId?.trim().length ? song.musicBrainzId.trim() : undefined,
            spotifyId: song.spotifyId?.trim().length ? song.spotifyId : undefined,
            hotspot: (song.hotspot || 0) > 0 ? song.hotspot : undefined,
        }),
    });

    return await resp.json();
};

export async function updateSong(song: Record<string, any>): Promise<Song> {
    const resp = await customFetch(`/api/songs/${song.id}`, {
        method: 'PATCH',
        body: JSON.stringify({
            title: song.title,
            artist: song.artist,
            format: song.format ? '/api/song_formats/' + song.format : undefined,
            quality: song.quality ? '/api/song_qualities/' + song.quality : undefined,
            musicBrainzId: song.musicBrainzId,
            spotifyId: song.spotifyId,
            hotspot: song.hotspot,
        }),
    });

    return await resp.json();
};

export async function compileSong(id: number): Promise<Song> {
    const resp = await customFetch(`/api/songs/${id}/compile`, {
        method: 'PATCH',
        body: '{}',
    });

    return await resp.json();
}

export async function decompileSong(id: number): Promise<Song> {
    const resp = await customFetch(`/api/songs/${id}/decompile`, {
        method: 'PATCH',
        body: '{}',
    });

    return await resp.json();
}