import { UploadFile } from "antd";

export class BackdropAlbum {
    id: number|null;
    title: string;
    author: string;
    version: number;

    backdrops: Backdrop[]|null;

    constructor(data?: Record<string, any>) {
        if (!data) {
            this.id = null;
            this.title = '';
            this.author = '';
            this.version = 1;
            this.backdrops = [];

            return;
        }

        this.id = data['id'];
        this.title = data['title'];
        this.author = data['author'];
        this.version = data['version'];

        this.backdrops = Backdrop.fromArray(data['backdrops']);
    }

    static fromJson(data?: Record<string, any>|null) {
        if (!data) {
            return null;
        }

        return new BackdropAlbum(data);
    }
}

export class Backdrop {
    id: number;
    title: string;
    file?: UploadFile;
    url: string;

    constructor(data?: Record<string, any>) {
        if (!data) {
            this.id = 0;
            this.title = '';
            this.url = '';

            return;
        }

        this.id = data['id'];
        this.title = data['title'];
        this.url = data['url'];
    }

    static fromJson(data?: Record<string, any>|null) {
        if (!data) {
            return null;
        }

        return new Backdrop(data);
    }

    static fromArray(data?: Record<string, any>[]|null) {
        if (!data) {
            return null;
        }

        return data.map(x => Backdrop.fromJson(x)).filter(x => !!x);
    }
}