import dayjs from "dayjs";

export default class PnPicture {
    iri: string;
    id: string;
    event: string;
    takenAt: dayjs.Dayjs;
    unattended: boolean;

    constructor(iri: string, id: string, event: string, takenAt: dayjs.Dayjs, unattended: boolean) {
        this.iri = iri;
        this.id = id;
        this.event = event;
        this.takenAt = takenAt;
        this.unattended = unattended;
    }

    static fromJson(data: Record<string, any> | null): PnPicture | null {
        if (!data) {
            return null;
        }

        return new PnPicture(
            data['@id'],
            data['id'],
            data['event'],
            dayjs(data['taken_at']),
            data['unattended'],
        );
    }
}