import { createFileRoute } from '@tanstack/react-router'
import { Route as ParentRoute } from './route';
import { t } from 'i18next';
import Title from '@/components/generic/title';

export const Route = createFileRoute('/_authenticated/$id/timelapse')({
  component: RouteComponent,
})

function RouteComponent() {
  const data = ParentRoute.useLoaderData();

  return <div className='flex flex-col gap-2'>
    <Title>
      {t('events.timelapse')}:
    </Title>

    {
      !data.export?.timelapse
      && <p>{t('events.no_timelapse')}</p>
    }

    {/*
    @TODO: Do something for it to fucking works everytime
    a lot of the time it just get 401 or stuff like that
    maybe we should use fetch+blob to ensure everything is OK
    idk
  */}

    {
      data.export?.timelapse
      && <video className='w-full' controls>
        <source src={`/api/events/${data.id}/timelapse`} type='video/mp4' />
        {t('events.no_timelapse')}
      </video>
    }
  </div>
}
