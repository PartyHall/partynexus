import PnSong, { PnExternalSong, PnSongRequest } from "./responses/song";

import { Collection } from "./responses/collection";
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

        if (song.musicBrainzId && song.musicBrainzId.length > 0) {
            body['musicBrainzId'] = song.musicBrainzId
        }

        if (song.spotifyId && song.spotifyId.length > 0) {
            body['spotifyId'] = song.spotifyId
        }

        const resp = await this.sdk.post('/api/songs', body);
        const data = await resp.json();

        return PnSong.fromJson(data);
    }

    async updateSong(song: PnSong): Promise<PnSong|null> {
        const body: any = {
            'title': song.title,
            'artist': song.artist,
            'format': song.format,
            'quality': song.quality,
            'hotspot': song.hotspot,
        };

        if (song.musicBrainzId && song.musicBrainzId.length > 0) {
            body['musicBrainzId'] = song.musicBrainzId
        }

        if (song.spotifyId && song.spotifyId.length > 0) {
            body['spotifyId'] = song.spotifyId
        }

        const resp = await this.sdk.patch(`/api/songs/${song.id}`, body);
        const data = await resp.json();

        return PnSong.fromJson(data);
    }

    async upsertSong(song: PnSong): Promise<PnSong|null> {
        if (song.id) {
            return this.updateSong(song);
        }

        return this.createSong(song);
    }

    async searchExternal(provider: string, artist: string, title: string): Promise<Collection<PnExternalSong>|null> {
        const resp = await this.sdk.get(`/api/external/${provider.toLowerCase()}/${artist}/${title}`)
        const data = await resp.json();
        
        return PnExternalSong.fromCollection(data);
    }

    async compile(song: PnSong): Promise<PnSong|null> {
        const resp = await this.sdk.patch(`/api/songs/${song.id}/compile`, {});
        const data = await resp.json();

        return PnSong.fromJson(data);
    }

    async decompile(song: PnSong): Promise<PnSong|null> {
        const resp = await this.sdk.patch(`/api/songs/${song.id}/decompile`, {});
        const data = await resp.json();

        return PnSong.fromJson(data);
    }

    async uploadFile(song: PnSong, filetype: string, file: any) {
        const fd = new FormData();
        fd.set('file', file);

        const resp = await this.sdk.request(
            `/api/songs/${song.id}/upload-file/${filetype}?XDEBUG_TRIGGER=1`,
            {
                method: 'POST',
                headers: {
                    Authorization: 'Bearer ' + this.sdk.token,
                },
                body: fd,
            }
        );

        const data = await resp.json();

        return PnSong.fromJson(data);
    }

    async requestMusic(title: string, artist: string) {
        const resp = await this.sdk.post(`/api/song_requests`, { title, artist });
        const data = await resp.json();

        return PnSongRequest.fromJson(data);
    }

    async getRequestedMusics(page: number = 1, query: string = '') {
        const rq = new URLSearchParams();

        if (query.length > 0) {
            rq.set('query', query);
        }

        if (page > 1) {
            rq.set('page', `${page}`);
        }

        const resp = await this.sdk.get(`/api/song_requests?` + rq.toString());
        const data = await resp.json();

        return Collection.fromJson(data, x => PnSongRequest.fromJson(x));
    }

    async markRequestAsDone(rq: PnSongRequest) {
        await this.sdk.delete(`/api/song_requests/${rq.id}`);
    }
}