import { getAllEvents } from '@/api/events';
import EventListCard from '@/components/event/event_list_card';
import { ButtonLink } from '@/components/generic/button';
import useTranslatedTitle from '@/hooks/useTranslatedTitle'
import { useAuthStore } from '@/stores/auth';
import { IconPlus } from '@tabler/icons-react';
import { useInfiniteQuery } from '@tanstack/react-query';
import { createFileRoute } from '@tanstack/react-router'
import React from 'react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useInView } from 'react-intersection-observer';

export const Route = createFileRoute('/_authenticated/')({
  component: RouteComponent,
})

/**
 * @TODO:
 * Implement @tanstack/virtual
 */

function RouteComponent() {
  const { t } = useTranslation();
  const isGranted = useAuthStore(state => state.isGranted);
  useTranslatedTitle('events.title');

  const { ref, inView } = useInView();
  const [totalEvents, setTotalEvents] = useState(-1);

  const {
    status,
    data,
    error,
    isFetchingNextPage,
    fetchNextPage,
  } = useInfiniteQuery({
    queryKey: ['events'],
    queryFn: async ({ pageParam = 0 }) => {
      const data = await getAllEvents({ pageParam, mineOnly: true });

      setTotalEvents(data.totalItems);

      return {
        data: data.member,
        currentPage: pageParam,
        previousPage: data.view?.previous ?? null,
        nextPage: data.view?.next ?? null,
      };
    },
    initialPageParam: 1,
    getPreviousPageParam: firstPage => firstPage.nextPage,
    getNextPageParam: lastPage => lastPage.nextPage,
  });

  useEffect(() => {
    if (inView) {
      fetchNextPage();
    }
  }, [fetchNextPage, inView]);


  return <div className='pageContainer sm:w-150! flex flex-col gap-4! flex-1 h-full'>
    <div className='flex-1 w-full overflow-y-auto'>
      {status === 'pending' && <div className='text-center'>{t('generic.loading')}</div>}
      {status === 'error' && <div className='text-center text-red-glow'>Error: {error.message}</div>}

      {/* Ajouter "Vous n'avez accès à aucun évènements" */}

      {
        (status !== 'pending' && status !== 'error')
        && <div className='flex flex-col gap-2 w-full overflow-y-auto'>
          {
            data.pages.map(page => <React.Fragment key={page.currentPage}>
              {page.data.map(event => <EventListCard key={event.id} event={event} />)}
            </React.Fragment>)
          }
          <div ref={ref} className='h-8 pt-2 w-full text-center text-red-glow'>
            {
              isFetchingNextPage
                ? t('generic.loading')
                : t('generic.no_more_results')
            }
          </div>
        </div>
      }
    </div>
    <div className='flex flex-row justify-between w-full items-center'>
      <span className='flex-1'>{t('events.amt_events', { amt: totalEvents >= 0 ? totalEvents : '?' })}</span>
      <div className='flex-end flex gap-2'>
        {
          isGranted('ROLE_EVENT_MAKER')
          && <ButtonLink to='/new'>
            <IconPlus size={19} />
            {t('events.editor.title_new')}
          </ButtonLink>
        }
      </div>
    </div>
  </div>;
}
