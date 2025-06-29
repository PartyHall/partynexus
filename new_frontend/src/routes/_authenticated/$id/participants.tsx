import Title from '@/components/generic/title'
import { createFileRoute } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next'
import { Route as ParentRoute } from './route';
import Participant from '@/components/event/participant';

export const Route = createFileRoute('/_authenticated/$id/participants')({
  component: RouteComponent,
})


function RouteComponent() {
  const { t } = useTranslation()
  const data = ParentRoute.useLoaderData();

  return <div>
    <Title>{t('events.participants')}</Title>

    <div className='flex flex-row items-center gap-2 mb-4'>
      <span>{t('events.editor.participants.add')}: </span>
      <select className="flex-1">
        <option value="">{t('generic.select')}</option>
      </select>
    </div>

    <ul>
      <Participant key={data.owner.id} event={data} participant={data.owner} owner />

      {data.participants.map((participant) => <Participant key={participant.id} event={data} participant={participant} />)}
    </ul>
  </div>
}
