import EventEditor from "../components/event_editor/EventEditor";
import Loader from "../components/Loader";
import { PnEvent } from "../sdk/responses/event";
import { SdkError } from "../sdk/responses/error";
import { Typography } from "antd";

import { useAsyncEffect } from "ahooks";
import { useAuth } from "../hooks/auth";
import { useParams } from "react-router-dom";
import { useState } from "react";
import { useTranslation } from "react-i18next";

export default function EditEventPage() {
    const { id } = useParams();
    const { api } = useAuth();
    const { t } = useTranslation();

    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [event, setEvent] = useState<PnEvent | null>(null);

    useAsyncEffect(async () => {
        if (!id) {
            setError('not_found');
            return;
        }

        try {
            setEvent(await api.events.get(id));
        } catch (e) {
            if (e instanceof SdkError && e.status == 404) {
                setError('not_found.event');
            } else {
                setError('unknown')
            }
        }

        setLoading(false);
    }, [id]);

    return <Loader loading={loading}>
        {!error && event && <EventEditor event={event} />}
        {error && <Typography.Title>{t('generic.error.' + error)}</Typography.Title>}
    </Loader>;
}