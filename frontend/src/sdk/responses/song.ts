import { Collection } from "./collection";

export default class PnSong {
    iri: string;
    id: number;

    title: string;
    artist: string;
    coverUrl?: string;
    format?: string;
    quality?: string;
    musicbrainzId?: string;
    spotifyId?: string;
    nexusBuildId?: string;
    hotspot?: number;

    ready: boolean;

    constructor(jsonData: Record<string, any>) {
        this.iri = jsonData['@id'];
        this.id = jsonData['id'];
        this.title = jsonData['title'];
        this.artist = jsonData['artist'];
        this.coverUrl = jsonData['coverUrl'];
        this.format = jsonData['format'];
        this.quality = jsonData['quality'];
        this.musicbrainzId = jsonData['musicBrainzId'];
        this.spotifyId = jsonData['spotifyId'];
        this.nexusBuildId = jsonData['nexusBuildId'];
        this.hotspot = jsonData['hotspot'];
        this.ready = jsonData['ready'];
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