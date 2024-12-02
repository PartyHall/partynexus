import { Backdrop, BackdropAlbum } from "./responses/backdrop";
import { Collection } from "./responses/collection";
import { SDK } from ".";

export class Backdrops {
    private sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async getAlbum(id: number): Promise<BackdropAlbum|null> {
        const resp = await this.sdk.get(`/api/backdrop_albums/${id}`);
        const data = await resp.json();

        return BackdropAlbum.fromJson(data);
    }

    async getAlbums(): Promise<Collection<BackdropAlbum>|null> {
        const resp = await this.sdk.get(`/api/backdrop_albums`);
        const data = await resp.json();

        return Collection.fromJson(data, x => BackdropAlbum.fromJson(x));
    }

    async createAlbum(album: BackdropAlbum) {
        const resp = await this.sdk.post(`/api/backdrop_albums`, {
            'title': album.title,
            'author': album.author,
            'version': album.version,
        });

        const data = await resp.json();

        return BackdropAlbum.fromJson(data);
    }

    async updateAlbum(album: BackdropAlbum) {
        const resp = await this.sdk.patch(`/api/backdrop_albums/${album.id}`, {
            'title': album.title,
            'author': album.author,
            'version': album.version,
        });

        const data = await resp.json();

        return BackdropAlbum.fromJson(data);
    }

    async upsertAlbum(album: BackdropAlbum) {
        if (!album.id) {
            return this.createAlbum(album);
        }

        return this.updateAlbum(album);
    }

    async deleteAlbum(albumId: number): Promise<void> {
        await this.sdk.delete(`/api/backdrop_albums/${albumId}`);
    }

    async getBackdrops(albumId: number): Promise<Collection<Backdrop>|null> {
        const resp = await this.sdk.get(`/api/backdrop_albums/${albumId}/backdrops`);
        const data = await resp.json();

        return Collection.fromJson(data, x => Backdrop.fromJson(x));
    }

    async getBackdrop(albumId: number, id: number): Promise<Backdrop|null> {
        const resp = await this.sdk.get(`/api/backdrop_albums/${albumId}/backdrops/${id}`);
        const data = await resp.json();

        return Backdrop.fromJson(data);
    }

    async createBackdrop(albumId: number, backdrop: Backdrop) {
        if (!backdrop.file || !backdrop.file.originFileObj) {
            throw 'The backdrop file should be filled to upload it';
        }

        const fd = new FormData();
        fd.set('title', backdrop.title);
        fd.set('album', `/api/backdrop_albums/${albumId}`);
        fd.set('file', backdrop.file.originFileObj);

        const resp = await this.sdk.request(
            `/api/backdrops`,
            {
                method: 'POST',
                body: fd,
            }
        );

        const data = await resp.json();

        return Backdrop.fromJson(data);
    }

    async updateBackdrop(albumId: number, backdrop: Backdrop) {
        const resp = await this.sdk.patch(`/api/backdrop_albums/${albumId}/backdrops/${backdrop.id}`, {
            'title': backdrop.title,
        });

        const data = await resp.json();

        return Backdrop.fromJson(data);
    }

    async deleteBackdrop(albumId: number, id: number): Promise<void> {
        await this.sdk.delete(`/api/backdrop_albums/${albumId}/backdrops/${id}`);
    }
}