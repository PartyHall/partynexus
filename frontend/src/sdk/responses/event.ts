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

    owner: string;

    constructor(iri: string, id: string, name: string, author: string|null, datetime: dayjs.Dayjs, location: string|null, owner: string) {
        this.iri = iri;
        this.id = id;
        this.name = name; 
        this.author = author;
        this.datetime = datetime;
        this.location = location;

        /**
         * @TODO: Amt images handtaken, amt images unattended
         */

        this.owner = owner;
    }

    static fromJson(data: Record<string, any>|null): PnEvent|null {
        if (!data) {
            return null;
        }

        return new PnEvent(
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