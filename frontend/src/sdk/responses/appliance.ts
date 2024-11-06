import dayjs from "dayjs";

export default class PnAppliance {
    id: number;
    name: string;
    hardwareId: string;
    apiToken: string;
    lastSeen: dayjs.Dayjs|null;

    constructor(data: Record<string, any>) {
        this.id = data['id'];
        this.name = data['name'];
        this.hardwareId = data['hardwareId'];
        this.apiToken = data['apiToken'];
        this.lastSeen = data['lastSeen'] ? dayjs(data['lastSeen']) : null;
    }

    static fromJson(data: Record<string, any>|null) {
        if (!data) {
            return data;
        }

        return new PnAppliance(data);
    }

    static fromArray(data: Record<string, any>[]|null) {
        if (!data) {
            return [];
        }

        const appliances = data.map(x => PnAppliance.fromJson(x));

        return appliances.filter(x => !!x);
    }
}