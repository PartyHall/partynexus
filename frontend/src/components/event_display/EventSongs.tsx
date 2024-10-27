import { PnEvent } from "../../sdk/responses/event";
import { Typography } from "antd";
import { useTranslation } from "react-i18next";

export default function EventSongs({ event }: { event: PnEvent }) {
    const { t } = useTranslation();

    return <>
        <Typography.Title>{t('event.karaoke.sessions')}</Typography.Title>
    </>
}