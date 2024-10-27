import { Button, Flex, Typography } from "antd";
import { useNavigate, useParams } from "react-router-dom"

import EventActionBar from "../components/event_display/EventActionBar";
import EventInfos from "../components/event_display/EventInfos";
import EventPictureBar from "../components/event_display/EventPictureBar";
import EventSongs from "../components/event_display/EventSongs";
import EventTimelapse from "../components/event_display/EventTimelapse";

import Loader from "../components/Loader";
import { PnEvent } from "../sdk/responses/event";
import { SdkError } from "../sdk/responses/error";
import { useAsyncEffect } from "ahooks";
import { useAuth } from "../hooks/auth";
import { useState } from "react";
import { useTranslation } from "react-i18next";

export default function ShowEventPage() {
    const { id } = useParams();
    const { api, isAdminOrEventOwner } = useAuth();
    const { t } = useTranslation();
    const navigate = useNavigate();

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

    const displayOwnerStuff = isAdminOrEventOwner(event);

    return <Loader loading={loading}>
        {
            error && <Typography.Title>{t('generic.error.' + error)}</Typography.Title>
        }
        {
            !error && event && <>
                <Flex justify="space-between" align="center" style={{ marginRight: '1em' }}>
                    <Typography.Title className="blue-glow">{event.name}</Typography.Title>
                    {displayOwnerStuff && <Button onClick={() => navigate(`/events/${event.id}/edit`)}>{t('event.edit')}</Button>}
                </Flex>

                <EventInfos event={event} displayOwnerStuff={displayOwnerStuff} />
                <EventActionBar event={event} displayOwnerStuff={displayOwnerStuff} />
                <EventPictureBar event={event} />

                <EventTimelapse event={event} />
                <EventSongs event={event} />
            </>
        }
    </Loader >
}
