import ExportDownloadButton from '@/components/event/export_download_button';
import { ButtonLink } from '@/components/generic/button';
import { KeyVal } from '@/components/generic/key_val';
import Title from '@/components/generic/title';
import Username from '@/components/username';
import { useAuthStore } from '@/stores/auth';
import { useEvent } from '@/stores/event';
import { IconEdit } from '@tabler/icons-react';
import { createFileRoute } from '@tanstack/react-router'
import dayjs from 'dayjs';
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/$id/')({
  component: RouteComponent,
})

function RouteComponent() {
  const tokenUser = useAuthStore(state => state.tokenUser);
  const event = useEvent();
  const { t } = useTranslation();

  return <div className='flex-1'>
    <Title className='text-center' noMargin>{t('events.about')}</Title>

    {
      tokenUser?.id === event.owner?.id
      && <KeyVal label='generic.id' tooltip='events.notice_id_appliance'>
        {event.id}
      </KeyVal>
    }

    {
      event.datetime
      && <KeyVal label='events.date'>
        {dayjs(event.datetime).format('L - LT')}
      </KeyVal>
    }
    {
      event.location
      && <KeyVal label='events.located_at'>
        {event.location}
      </KeyVal>
    }
    {
      <KeyVal label='events.made_by'>
        {event.author?.length ? event.author : <Username user={event.owner} noStyle />}
      </KeyVal>
    }

    <div className='flex flex-col gap-2 mt-4'>
      {
        tokenUser?.id === event.owner?.id
        && <ButtonLink to='/$id/edit' params={{ id: event.id }}><IconEdit size={18} /> {t('generic.edit')}</ButtonLink>
      }
      {
        event.export
        && <ExportDownloadButton event={event} />
      }
    </div>
  </div>
}
