import { Button, Collapse, Flex, Typography } from "antd";
import { useAsyncEffect, useTitle } from "ahooks";
import { useNavigate, useParams } from "react-router-dom"

import EventActionBar from "../components/event_display/EventActionBar";
import EventExportBar from "../components/event_display/EventExportBar";
import EventInfos from "../components/event_display/EventInfos";
import EventPictureBar from "../components/event_display/EventPictureBar";
import EventSongs from "../components/event_display/EventSongs";

import Loader from "../components/Loader";
import { PnEvent } from "../sdk/responses/event";
import { SdkError } from "../sdk/responses/error";
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
    const [pageName, setPageName] = useState<string>(t('event.show_one'));

    useTitle(pageName + ' - PartyHall');

    useAsyncEffect(async () => {
        if (!id) {
            setError('not_found');
            return;
        }

        setLoading(true);
        try {
            const event = await api.events.get(id);
            setEvent(event);
            setPageName(event?.name ?? t('event.show_one'));
        } catch (e) {
            if (e instanceof SdkError && e.status == 404) {
                setError('not_found.event');
            } else {
                setError('unknown')
            }
        }
        setLoading(false);
    }, [id]);

    const displayOwnerStuff = isAdminOrEventOwner(event);

    const infosItems = [];
    if (event) {
        infosItems.push({
            key: 'infos',
            label: t('event.infos'),
            children: <EventInfos event={event} displayOwnerStuff={displayOwnerStuff} />
        })
    }

    if (isAdminOrEventOwner(event) && event?.export) {
        infosItems.push({
            key: 'export',
            label: t('event.export.title'),
            children: <EventExportBar pnExport={event.export} />
        });
    }

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

                <Collapse items={infosItems} defaultActiveKey={'infos'} />
                <EventActionBar event={event} setEvent={setEvent} displayOwnerStuff={displayOwnerStuff} />
                <EventPictureBar event={event} />

                <EventSongs event={event} />
            </>
        }
    </Loader >
}
