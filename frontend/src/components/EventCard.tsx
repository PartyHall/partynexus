import { Card, Flex, Typography } from "antd";
import { Link } from "react-router-dom";
import { PnListEvent } from "../sdk/responses/event"
import { useTranslation } from "react-i18next";

export default function EventCard({ event }: { event: PnListEvent }) {
    const { t } = useTranslation();

    return <Card
        size="small"
        title={event.name}
        extra={<Link to={`/events/${event.id}`}>See</Link>}
        style={{width: '15em'}}
    >
        <Flex vertical>
            <Typography.Text>{t('event.by', { author: event.author })}</Typography.Text>
            <Typography.Text>{t('event.at', { location: event.location })}</Typography.Text>
        </Flex>
    </Card>;
}