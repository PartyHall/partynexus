import type { User } from "./user";

export type Song = {
    id: number;
    title: string;
    artist: string;
    format: string;
    quality: string;
    spotifyId: string|null;
    nexusBuildId: string|null;
    duration: number;
    ready: boolean;
    cover: boolean;
    coverUrl: string|null;
    vocals: boolean;
    combined: boolean;
}

export type SongRequest = {
    id: number;
    title: string;
    artist: string;
    requestedBy: User;
};