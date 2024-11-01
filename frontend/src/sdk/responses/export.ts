import dayjs from "dayjs";

export default class PnExport {
    id: number;
    startedAt: dayjs.Dayjs;
    endedAt: dayjs.Dayjs|null;
    progress: string;
    status: string;

    constructor(data: Record<string, any>) {
        this.id = data['id'];
        this.startedAt = dayjs(data['startedAt']);
        this.endedAt = data['endedAt'] ? dayjs(data['endedAt']) : null;
        this.progress = data['progress'];
        this.status = data['status'];
    }

    static fromJson(data: Record<string, any>|null) {
        if (!data) {
            return null;
        }

        return new PnExport(data);
    }
}