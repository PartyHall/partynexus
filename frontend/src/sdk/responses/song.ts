import { Collection } from "./collection";
import { User } from "./user";
import dayjs from "dayjs";

export default class PnSong {
    iri: string;
    id: number;

    title: string;
    artist: string;
    coverUrl?: string;
    format?: string;
    quality?: string;
    musicBrainzId?: string;
    spotifyId?: string;
    nexusBuildId?: string;
    hotspot?: number;

    ready: boolean;

    vocals: boolean;
    full: boolean;

    constructor(jsonData: Record<string, any>) {
        this.iri = jsonData['@id'];
        this.id = jsonData['id'];
        this.title = jsonData['title'];
        this.artist = jsonData['artist'];
        this.coverUrl = jsonData['coverUrl'];
        this.format = jsonData['format'];
        this.quality = jsonData['quality'];
        this.musicBrainzId = jsonData['musicBrainzId'];
        this.spotifyId = jsonData['spotifyId'];
        this.nexusBuildId = jsonData['nexusBuildId'];
        this.hotspot = jsonData['hotspot'];
        this.ready = jsonData['ready'];
        this.vocals = jsonData['vocals'];
        this.full = jsonData['combined'];
    }

    public static fromJson(data: Record<string,any>|null): PnSong|null {
        if (!data) {
            return null;
        }

        return new PnSong(data);
    }

    public static fromCollection(data: Record<string, any>|null): Collection<PnSong>|null {
        if (!data) {
            return null;
        }

        return Collection.fromJson<PnSong>(data, x => PnSong.fromJson(x));
    }
}

export class PnExternalSong {
    id: string;
    title: string|null;
    artist: string|null;
    cover: string|null;

    constructor(data: Record<string, any>) {
        this.id = data['id'];
        this.title = data['title'];
        this.artist = data['artist'];
        this.cover = data['cover'];
    }

    static fromJson(data: Record<string, any>|null): PnExternalSong|null {
        if (!data) {
            return null;
        }

        return new PnExternalSong(data);
    }

    public static fromCollection(data: Record<string, any>|null): Collection<PnExternalSong>|null {
        if (!data) {
            return null;
        }

        return Collection.fromJson<PnExternalSong>(data, x => PnExternalSong.fromJson(x));
    }
}

export class PnSongRequest {
    id: number;
    title: string;
    artist: string;
    requestedBy: User|null;
    createdAt: dayjs.Dayjs;
    updatedAt: dayjs.Dayjs|null;

    constructor(data: Record<string, any>) {
        this.id = data['id'];
        this.title = data['title'];
        this.artist = data['artist'];
        this.requestedBy = User.fromJson(data['requestedBy']);
        this.createdAt = dayjs(data['createdAt']);
        this.updatedAt = dayjs(data['updatedAt']);
    }

    static fromJson(data: Record<string, any>|null) {
        if (!data) {
            return null;
        }

        return new PnSongRequest(data);
    }
}

export class PnSongSession {
    id: number;
    title: string;
    artist: string;
    song: string|null;
    singer: string;
    applianceId: number|null;
    sungAt: dayjs.Dayjs;

    constructor(data: Record<string, any>) {
        this.id = data['id'];
        this.title = data['title'];
        this.artist = data['artist'];
        this.song = data['song'] ?? null;
        this.singer = data['singer'];
        this.applianceId = data['applianceId'] ?? null;
        this.sungAt = dayjs(data['sungAt']);
    }

    static fromJson(data: Record<string, any>|null) {
        if (!data) {
            return null;
        }

        return new PnSongSession(data);
    }
}