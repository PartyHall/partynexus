import { Flex, Typography } from "antd";

import { PnEvent } from "../../sdk/responses/event";
import { useTranslation } from "react-i18next";

export default function EventSongs({ event }: { event: PnEvent }) {
    const { t } = useTranslation();

    return <Flex vertical gap={8}>
        <Typography.Title className="red-glow ml1-2">{t('event.karaoke.sessions')}</Typography.Title>
        <Typography.Text>{t('event.karaoke.no_sung_songs')}</Typography.Text>
    </Flex>
}