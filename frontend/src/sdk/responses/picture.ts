import dayjs from "dayjs";

export default class PnPicture {
    id: string;
    event: string;
    takenAt: dayjs.Dayjs;
    unattended: boolean;

    constructor(id: string, event: string, takenAt: dayjs.Dayjs, unattended: boolean) {
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
            data['id'],
            data['event'],
            dayjs(data['taken_at']),
            data['unattended'],
        );
    }
}