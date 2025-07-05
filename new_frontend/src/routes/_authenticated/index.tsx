import { getAllEvents } from '@/api/events';
import EventListCard from '@/components/event/event_list_card';
import { ButtonLink } from '@/components/generic/button';
import InfiniteScroll from '@/components/generic/infinite_scroll';
import useTranslatedTitle from '@/hooks/useTranslatedTitle'
import { useAuthStore } from '@/stores/auth';
import { IconPlus } from '@tabler/icons-react';
import { createFileRoute } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/')({
  component: RouteComponent,
})

function RouteComponent() {
  useTranslatedTitle('events.title');

  const { t } = useTranslation();
  const isGranted = useAuthStore(state => state.isGranted);

  return <InfiniteScroll
    queryKey={['events']}
    fetchData={async params => await getAllEvents({ ...params, mineOnly: true })}
    renderItem={event => <EventListCard key={event.id} event={event} />}
    totalTranslationKey='events.amt_events'
    bottomButtons={[
      isGranted('ROLE_EVENT_MAKER') && <ButtonLink to='/new' key='new-event'>
        <IconPlus size={19} />
        {t('events.editor.title_new')}
      </ButtonLink>
    ]}
  />;
}
