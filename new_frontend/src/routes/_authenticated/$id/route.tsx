import { getEventById } from '@/api/events';
import { ButtonLink } from '@/components/generic/button';
import Card from '@/components/generic/card';
import Username from '@/components/username';
import useTitle, { useNexusTitle } from '@/hooks/useTitle';
import { useAuthStore } from '@/stores/auth';
import { IconEdit } from '@tabler/icons-react';
import { createFileRoute, Link, Outlet, useRouter } from '@tanstack/react-router'
import dayjs from 'dayjs';
import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/$id')({
  component: RouteComponent,
  loader: async ({ params }) => {
    if (!params.id) {
      throw new Error('Missing required parameter: id');
    }

    return await getEventById(params.id);
  },
})

function KeyVal({ label, children }: { label: string, children: ReactNode }) {
  const { t } = useTranslation();

  return <p className='flex flex-col'>
    <span className='font-bold text-pink-glow'>{t(label)}:</span>
    <span className='ml-4'>{children}</span>
  </p>;
}

type Subroute = '' | '/timelapse' | '/participants' | '/songs' | '/settings';

function SubrouteLink({ text, to, id }: { text: string, to: Subroute, id: string }) {
  const { t } = useTranslation();
  const route = useRouter();
  const isActive = route.state.location.pathname === `/${id}${to}`;

  return isActive
    ? <span className='bg-synthbg-900 p-2 rounded-md'>{t(text)}</span>
    : <Link className='m-2' to={`/$id${to}`} params={{ id: id }} replace>{t(text)}</Link>;

}

function RouteComponent() {
  const data = Route.useLoaderData();
  const { t } = useTranslation();

  useNexusTitle(data.name);

  const tokenUser = useAuthStore(state => state.tokenUser);

  return <div className='pageContainer gap-3!'>
    <Card className='w-full'>
      <div className='flex flex-row justify-between items-start'>
        <h1 className='text-2xl font-bold mb-4 text-blue-glow'>{data.name}</h1>

        {
          tokenUser?.id === data.owner?.id
          && <ButtonLink to='/$id/edit' params={{ id: data.id }}><IconEdit size={18} /> {t('generic.edit')}</ButtonLink>
        }
      </div>
      {
        data.datetime
        && <KeyVal label='events.date'>
          {dayjs(data.datetime).format('L - LT')}
        </KeyVal>
      }
      {
        data.location
        && <KeyVal label='events.located_at'>
          {data.location}
        </KeyVal>
      }
      <KeyVal label='events.made_by'>
        {data.author ?? <Username user={data.owner} noStyle />}
      </KeyVal>
    </Card >

    <Card className='w-full flex flex-row flex-wrap items-center justify-around p-2 bg-synthbg-700 rounded-md text-red-glow'>
      <SubrouteLink text='events.photos' to='' id={data.id} />
      {
        data.export?.timelapse
        && <SubrouteLink text='events.timelapse' to='/timelapse' id={data.id} />
      }
      <SubrouteLink text='events.participants' to='/participants' id={data.id} />
      <SubrouteLink text='events.sung_songs' to='/songs' id={data.id} />
      {
        tokenUser?.id === data.owner?.id
        && <SubrouteLink text='events.settings' to='/settings' id={data.id} />
      }
    </Card>

    <Card className='w-full overflow-y-auto'>
      <Outlet />
    </Card>
  </div >
}
