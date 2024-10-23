import EventActionBar from "../components/event_display/EventActionBar";
import EventInfos from "../components/event_display/EventInfos";
import EventPictureBar from "../components/event_display/EventPictureBar";
import Loader from "../components/Loader";
import { PnEvent } from "../sdk/responses/event";
import { SdkError } from "../sdk/responses/error";
import { Typography } from "antd";
import { useAsyncEffect } from "ahooks";
import { useAuth } from "../hooks/auth";
import { useParams } from "react-router-dom"
import { useState } from "react";
import { useTranslation } from "react-i18next";

export default function ShowEventPage() {
    const { id } = useParams();
    const { api, isGranted } = useAuth();
    const { t } = useTranslation();

    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [event, setEvent] = useState<PnEvent | null>(null);

    useAsyncEffect(async () => {
        if (!id) {
            setError('not_found');
            return;
        }

        setLoading(true);
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
    }, [id])

    /* @TODO: Admin or if user is owner of the event */
    const displayOwnerStuff = isGranted('ROLE_ADMIN');

    return <Loader loading={loading}>
        {
            error && <Typography.Title>{t('generic.error.' + error)}</Typography.Title>
        }
        {
            !error && event && <>
                <Typography.Title className="blue-glow">{event.name}</Typography.Title>

                <EventInfos event={event} displayOwnerStuff={displayOwnerStuff} />
                <EventActionBar event={event} displayOwnerStuff={displayOwnerStuff} />
                <EventPictureBar event={event} />

                {/* Only if the event is finished & the timelapse is generated */}
                <Typography.Title>{t('event.pictures.timelapse')}</Typography.Title>

                <Typography.Title>{t('event.karaoke.sessions')}</Typography.Title>
            </>
        }
    </Loader>
}
