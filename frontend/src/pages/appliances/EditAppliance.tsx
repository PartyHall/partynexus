import ApplianceEditor from "../../components/account/ApplianceEditor";
import Loader from "../../components/Loader";
import PnAppliance from "../../sdk/responses/appliance";
import { Typography } from "antd";

import { useAsyncEffect } from "ahooks";
import { useAuth } from "../../hooks/auth";
import { useParams } from "react-router-dom";
import { useState } from "react";

export default function EditAppliancePage() {
    const {id} = useParams();
    const [loading, setLoading] = useState<boolean>(true);
    const [appliance, setAppliance] = useState<PnAppliance | null>(null);

    const {api} = useAuth();

    useAsyncEffect(async () => {
        if (!id) {
            return;
        }

        setLoading(true);

        const data = await api.appliances.get(id);
        setAppliance(data);

        setLoading(false);
    }, []);

    return <Loader loading={loading}>
        { appliance && <ApplianceEditor appliance={appliance} /> }
        { !appliance && <Typography.Title>Appliance not found</Typography.Title>}
    </Loader>
}