import type { User } from "./user";

export type Song = {
    id?: number | null;
    title: string;
    artist: string;
    format: string;
    quality: string;
    musicBrainzId: string|null;
    spotifyId: string|null;
    nexusBuildId: string|null;
    duration: number;
    hotspot: number;
    ready: boolean;
    cover: boolean;
    coverUrl: string|null;
    vocals: boolean;
    combined: boolean;
}

export type UpsertSong = Song & { coverFile: File | null };

export type SongRequest = {
    id: number;
    title: string;
    artist: string;
    requestedBy: User;
};

export type ExternalSong = {
    id: string;
    title: string;
    artist: string;
    cover?: string;
};

export type SongSession = {
    id: number;
    title: string;
    artist: string;
    sungAt: string;
    singer: string;
};