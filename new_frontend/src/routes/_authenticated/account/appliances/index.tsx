import { getUser } from '@/api/users';
import ApplianceCard from '@/components/account/appliances/appliance_card';
import { ButtonLink } from '@/components/generic/button';
import Card from '@/components/generic/card';
import Title from '@/components/generic/title';
import useTranslatedTitle from '@/hooks/useTranslatedTitle'
import { useAuthStore } from '@/stores/auth';
import { IconPlus } from '@tabler/icons-react';
import { createFileRoute, useRouter } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/account/appliances/')({
  component: RouteComponent,
  loader: async () => {
    const tokenUser = useAuthStore.getState().tokenUser;
    if (!tokenUser) {
      throw new Error('User not authenticated');
    }

    return (await getUser(tokenUser.id)).appliances;
  },
})

function RouteComponent() {
  const router = useRouter();

  const { t } = useTranslation();
  const isGranted = useAuthStore(state => state.isGranted);
  useTranslatedTitle('account.my_appliances.title');

  const data = Route.useLoaderData();

  return <div className='pageContainer'>
    <Card className='w-full sm:w-150 gap-2! p-4'>
      <div className='w-full flex flex-row items-center justify-between mb-3'>
        <Title noMargin>{t('account.my_appliances.title')}</Title>
        {
          isGranted('ROLE_EVENT_MAKER')
          && <ButtonLink to='/account/appliances/new'>
            <IconPlus />
            {t('generic.new')}
          </ButtonLink>
        }
      </div>
      {
        data.length === 0 && (
          <div className='text-center text-red-glow mb-4'>
            {t('account.my_appliances.none_found')}
          </div>
        )
      }

      {
        data.map(appliance => (
          <ApplianceCard
            key={appliance.id}
            appliance={appliance}
            doInvalidateRouter={() => router.invalidate()}
          />
        ))
      }
    </Card>
  </div>;
}
