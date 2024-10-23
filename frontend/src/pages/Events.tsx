import { Flex, Menu, Typography } from "antd";
import { Collection } from "../sdk/responses/collection";
import EventCard from "../components/EventCard";
import Loader from "../components/Loader";
import { PlusOutlined } from '@ant-design/icons';
import { PnListEvent } from "../sdk/responses/event";
import { useAsyncEffect } from "ahooks";
import { useAuth } from "../hooks/auth";
import { useNavigate } from "react-router-dom";
import { useState } from "react";
import { useTranslation } from "react-i18next";

export default function EventsPage() {
    const { api, isGranted } = useAuth();
    const { t } = useTranslation();
    const navigate = useNavigate();

    const [loaded, setLoaded] = useState<boolean>(false);
    const [events, setEvents] = useState<Collection<PnListEvent> | null>(null);

    useAsyncEffect(async () => {
        setLoaded(false);
        setEvents(await api.events.getCollection(1, api.tokenUser?.iri ?? ''));
        setLoaded(true);
    }, []);

    const menu = [];

    if (isGranted('ROLE_ADMIN')) {
        menu.push({
            label: t('event.create_bt'),
            key: '/events/new',
            icon: <PlusOutlined />
        });
    }

    return <Loader loading={!loaded}>
        {
            (isGranted('ROLE_ADMIN')) && <Menu
                mode="horizontal"
                items={menu}
                style={{justifyContent: 'center'}}
                onClick={x => navigate(x.key)}
            />
        }

        {
            events &&
            <Flex style={{ height: 'min-content' }}>
                <Flex gap={16} align="start" justify="center" style={{alignItems: 'stretch'}} wrap>
                    {events.items.map(x => <EventCard key={x.iri} event={x} />)}
                </Flex>
            </Flex>

        }

        {
            (!events || events.total === 0) &&
            <Flex justify="center" style={{ width: '100%', paddingTop: '15em' }}>
                <Typography.Title type="danger">
                    {t('event.no_events')}
                </Typography.Title>
            </Flex>
        }
    </Loader>;
}
