import PnAppliance from './responses/appliance';
import { SDK } from '.';

export class Appliances {
    private sdk: SDK;

    constructor(sdk: SDK) {
        this.sdk = sdk;
    }

    async get(id: string): Promise<PnAppliance | null> {
        const resp = await this.sdk.get(`/api/appliances/${id}`);
        const data = await resp.json();

        return PnAppliance.fromJson(data);
    }

    async create(appliance: PnAppliance): Promise<PnAppliance | null> {
        const resp = await this.sdk.post(`/api/appliances`, appliance);
        const data = await resp.json();

        return PnAppliance.fromJson(data);
    }

    async update(appliance: PnAppliance): Promise<PnAppliance | null> {
        const resp = await this.sdk.patch(
            `/api/appliances/${appliance.id}`,
            appliance
        );
        const data = await resp.json();

        return PnAppliance.fromJson(data);
    }

    async upsert(appliance: PnAppliance): Promise<PnAppliance | null> {
        if (appliance.id) {
            return this.update(appliance);
        }

        return this.create(appliance);
    }

    async delete(id: number) {
        await this.sdk.delete(`/api/appliances/${id}`);
    }
}
