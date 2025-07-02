import type { EventListItem } from "@/types/event";
import { CardLink } from "../generic/card";
import Username from "../username";
import { useTranslation } from "react-i18next";

import dayjs from 'dayjs';

type Props = {
    event: EventListItem;
};

export default function EventListCard({ event }: Props) {
    const { t } = useTranslation();

    return <CardLink noGlow to='/$id' params={{id: event.id}}>
        <h3 className='text-xl font-semibold text-pink-glow'>{event.name}</h3>
        {event.datetime && <p>{dayjs(event.datetime).format('L - LT')}</p>}
        <p>{t('events.made_by')} {event.author?.length ? event.author : <Username user={event.owner} noStyle />}</p>
        {event.location && <p>{t('events.located_at')}: {event.location}</p>}
    </CardLink>
}