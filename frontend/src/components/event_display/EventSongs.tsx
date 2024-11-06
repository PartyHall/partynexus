import { Flex, Typography } from "antd";
import { PnEvent } from "../../sdk/responses/event";
import SearchablePaginatedList from "../SearchablePaginatedList";
import SongSession from "./SongSession";

import { useAuth } from "../../hooks/auth";
import { useTranslation } from "react-i18next";

export default function EventSongs({ event }: { event: PnEvent }) {
    const { t } = useTranslation();
    const { api } = useAuth();

    return <Flex vertical gap={8}>
        <Typography.Title className="red-glow ml1-2">{t('event.karaoke.sessions')}</Typography.Title>
        <SearchablePaginatedList
            doSearch={async (_: string, page: number) => await api.events.getSongSessions(event, page)}
            showSearch={false}
            renderElement={x => <SongSession key={x.id} session={x} />}
            noResults="event.karaoke.no_sung_songs"
        />
    </Flex>
}