import PnExport from "./export";
import { User } from "./user";
import dayjs from "dayjs";

export class PnListEvent {
    iri: string;
    id: string;
    name: string;
    author: string|null;
    datetime: dayjs.Dayjs
    location: string|null;

    owner: string;

    constructor(iri: string, id: string, name: string, author: string|null, datetime: dayjs.Dayjs, location: string|null, owner: string) {
        this.iri = iri;
        this.id = id;
        this.name = name; 
        this.author = author;
        this.datetime = datetime;
        this.location = location;

        this.owner = owner;
    }

    static fromJson(data: Record<string, any>|null): PnListEvent|null {
        if (!data) {
            return null;
        }

        return new PnListEvent(
            data['@id'],
            data['id'],
            data['name'],
            data['author'],
            dayjs(data['datetime']),
            data['location'],
            data['owner'],
        );
    }
}

export class PnEvent {
    iri: string;
    id: string;
    name: string;
    author: string|null;
    datetime: dayjs.Dayjs
    location: string|null;

    over: boolean;

    owner: User;
    participants: User[];

    export: PnExport|null;

    constructor(data: Record<string, any>) {
        const owner = User.fromJson(data['owner']);
        if (!owner) {
            throw 'No owner in the response!';
        }

        this.iri = data['@id'];
        this.id = data['id'];
        this.name = data['name'];
        this.author = data['author'];
        this.datetime = dayjs(data['datetime']);
        this.location = data['location'];
        this.owner = owner;
        this.over = data['over'];

        this.participants = User.fromArray(data['participants']);

        this.export = PnExport.fromJson(data['export']);

        /**
         * @TODO: Amt images handtaken, amt images unattended
         */
    }

    static fromJson(data: Record<string, any>|null): PnEvent|null {
        if (!data) {
            return null;
        }

        return new PnEvent(data);
    }
}