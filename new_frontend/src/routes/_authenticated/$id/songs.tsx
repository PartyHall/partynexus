import { getSongSessions } from '@/api/events/karaoke';
import Card from '@/components/generic/card';
import Title from '@/components/generic/title';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { createFileRoute } from '@tanstack/react-router'
import dayjs from 'dayjs';
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/$id/songs')({
  component: RouteComponent,
  loader: async ({ params }) => {
    if (!params.id) {
      throw new Error('Event ID is required');
    }

    return await getSongSessions(params.id);
  },
})

function RouteComponent() {
  const { t } = useTranslation();
  useTranslatedTitle('events.title');

  const sessions = Route.useLoaderData();

  return <div className='flex flex-col gap-4! flex-1 h-full text-center overflow-y-auto pb-2'>
    <Title noMargin>{t('events.karaoke.title')}</Title>
    {sessions.totalItems === 0 && <span>{t('events.karaoke.no_sessions')}</span>}
    {sessions.totalItems !== 0 && <span>{t('events.karaoke.amt_song_session', {amt: sessions.totalItems})}</span>}

    {
      sessions.member.map((s) => {
        const date = dayjs(s.sungAt);

        return <Card key={s.id} className='flex flex-col w-full bg-synthbg-700! p-2!' noGlow>
          <span className='text-primary-300'>{t('events.karaoke.song_title', s)}</span>
          <span className='text-primary-100'>{t('events.karaoke.interpreted_by', s)}</span>
          <span className='text-primary-100'>{t('events.karaoke.sung_at', {date: date.format('L'), time: date.format('LT')})}</span>
        </Card>;
      })
    }
  </div>;
}
