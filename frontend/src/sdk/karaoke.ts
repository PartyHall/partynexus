import { Collection } from "./responses/collection";
import PnSong from "./responses/song";
import { SDK } from ".";

export default class Karaoke {
    private sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async getCollection(page: number = 1, search?: string, ready: boolean = true): Promise<Collection<PnSong>|null> {
        let url = `/api/songs?page=${page}`

        if (search) {
            url += `&search=${search}`;
        }

        url += `&ready=${ready}`

        const resp = await this.sdk.get(url);
        const data = await resp.json()

        return PnSong.fromCollection(data);
    }

    async getSong(id: number): Promise<PnSong|null> {
        const resp = await this.sdk.get(`/api/songs/${id}`);
        const data = await resp.json();

        return PnSong.fromJson(data);
    }

    async createSong(song: PnSong): Promise<PnSong|null> {
        const body: any = {
            'title': song.title,
            'artist': song.artist,
            'format': song.format,
            'quality': song.quality,
            'hotspot': song.hotspot,
        };

        if (song.musicbrainzId && song.musicbrainzId.length > 0) {
            body['musicBrainzId'] = song.musicbrainzId
        }

        if (song.spotifyId && song.spotifyId.length > 0) {
            body['spotifyId'] = song.spotifyId
        }

        const resp = await this.sdk.post('/api/songs', body);
        const data = await resp.json();

        return PnSong.fromJson(data);
    }

    async updateSong(song: PnSong): Promise<PnSong|null> {
        return null;
    }

    async upsertSong(song: PnSong): Promise<PnSong|null> {
        if (song.id) {
            return this.updateSong(song);
        }

        return this.createSong(song);
    }
}