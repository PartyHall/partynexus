import type { Collection } from "@/types";
import type { Song, UpsertSong } from "@/types/karaoke";
import { customFetch } from "../customFetch";

type GetCollectionParams = {
    pageParam?: number;
    search?: string;
    ready?: boolean;
    vocals?: boolean | null;
    format?: string[];
};

export async function getSongCollection({ pageParam = 1, search, ready, vocals, format }: GetCollectionParams): Promise<Collection<Song>> {
    const params = new URLSearchParams({
        page: String(pageParam),
        ready: ready ? 'true' : 'false',
    });

    if (search) {
        params.set('search', search);
    }

    if (vocals !== null && vocals !== undefined) {
        params.set('vocals', vocals ? 'true' : 'false');
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

export async function createSong(song: UpsertSong): Promise<Song> {
    const formData = new FormData();

    formData.set('title', song.title);
    formData.set('artist', song.artist);
    formData.set('format', song.format);
    formData.set('quality', song.quality);

    if (song.musicBrainzId?.trim().length) {
        formData.set('musicBrainzId', song.musicBrainzId.trim());
    }

    if (song.spotifyId?.trim().length) {
        formData.set('spotifyId', song.spotifyId.trim());
    }

    if (song.hotspot && song.hotspot > 0) {
        formData.set('hotspot', String(song.hotspot));
    }

    if (song.coverFile) {
        formData.set('coverFile', song.coverFile);
    }

    const resp = await customFetch('/api/songs', {
        method: 'POST',
        body: formData,
    });

    return await resp.json();
};

export async function updateSong(song: Record<string, any>): Promise<Song> {
    const formData = new FormData();

    if (song.title) {
        formData.set('title', song.title);
    }

    if (song.artist) {
        formData.set('artist', song.artist);
    }

    if (song.format) {
        formData.set('format', song.format);
    }

    if (song.quality) {
        formData.set('quality', song.quality);
    }

    if (song.musicBrainzId !== undefined) {
        formData.set('musicBrainzId', song.musicBrainzId);
    }

    if (song.spotifyId !== undefined) {
        formData.set('spotifyId', song.spotifyId);
    }

    if (song.hotspot !== undefined) {
        formData.set('hotspot', String(song.hotspot));
    }

    if (song.coverFile) {
        formData.set('coverFile', song.coverFile);
    }

    const resp = await customFetch(`/api/songs/${song.id}`, {
        method: 'POST',
        body: formData,
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

export async function uploadFile(song: Song, filetype: string, file: File): Promise<Song> {
    const fd = new FormData();
    fd.set('file', file);

    const resp = await customFetch(
        `/api/songs/${song.id}/upload-file/${filetype}`,
        { method: 'POST', headers: { 'Accept': 'application/ld+json' }, body: fd },
    );

    return await resp.json();
}
