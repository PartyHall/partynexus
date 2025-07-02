import Title from '@/components/generic/title'
import { createFileRoute, useRouter } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next';
import { Route as ParentRoute } from './route';
import SettingsExport from '@/components/event/settings/export';
import ConfirmButton from '@/components/generic/confirm_button';
import concludeEvent from '@/api/events/conclude';
import { enqueueSnackbar } from 'notistack';
import { useEvent } from '@/stores/event';
import SettingsDisplayBoard from '@/components/event/settings/display_board';
import SettingsConclude from '@/components/event/settings/conclude';

export const Route = createFileRoute('/_authenticated/$id/settings')({
  component: RouteComponent,
})

function RouteComponent() {
  const { t } = useTranslation();
  const event = useEvent();

  return <div className='flex flex-col gap-4'>
    <Title className='text-center' noMargin>{t('events.settings.title')}</Title>

    <SettingsDisplayBoard />

    {!event.over && <SettingsConclude eventId={event.id} eventName={event.name} />}

    {
      event.over
      && <SettingsExport eventId={event.id} exp={event.export} />
    }
  </div>
}
