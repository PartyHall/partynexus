import { Flex, Menu, Typography } from "antd";
import { useAsyncEffect, useTitle } from "ahooks";

import { Collection } from "../sdk/responses/collection";
import EventCard from "../components/EventCard";
import { IconSquareRoundedPlus } from "@tabler/icons-react";
import Loader from "../components/Loader";
import { PnListEvent } from "../sdk/responses/event";
import SearchablePaginatedList from "../components/SearchablePaginatedList";

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

    useTitle(t('generic.home') + ' - PartyHall');

    useAsyncEffect(async () => {
        setLoaded(false);
        setEvents(await api.events.getCollection(1));
        setLoaded(true);
    }, []);

    const menu = [];

    if (isGranted('ROLE_ADMIN')) {
        menu.push({
            label: t('event.create_bt'),
            key: '/events/new',
            icon: <IconSquareRoundedPlus size={20} />
        });
    }

    return <Loader loading={!loaded}>
        {
            events && events.total > 0 &&
            <Flex style={{ height: '100%', overflowY: 'auto' }}>
                <SearchablePaginatedList
                    className="EventList"
                    doSearch={async (query: string, page: number) => api.events.getCollection(
                        page,
                        query,
                    )}
                    renderElement={(x: PnListEvent) => <EventCard key={x.iri} event={x} />}
                    extraActions={<Flex>
                        {
                            (isGranted('ROLE_ADMIN')) && <Menu
                                mode="horizontal"
                                items={menu}
                                style={{ justifyContent: 'center' }}
                                onClick={x => navigate(x.key)}
                            />
                        }
                    </Flex>}
                />
            </Flex>
        }

        {
            (!events || events.total === 0) &&
            <Flex vertical align="center" justify="center" style={{ width: '100%', paddingTop: '15em' }}>
                <Typography.Title type="danger">
                    {t('event.no_events')}
                </Typography.Title>
                {
                    (isGranted('ROLE_ADMIN')) && <Menu
                        mode="horizontal"
                        items={menu}
                        style={{ justifyContent: 'center' }}
                        onClick={x => navigate(x.key)}
                    />
                }
            </Flex>
        }
    </Loader>;
}
