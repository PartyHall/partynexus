import { getUser } from '@/api/users';
import UserEditForm from '@/components/account/user_form';
import Card from '@/components/generic/card';
import Title from '@/components/generic/title';
import type { User } from '@/types/user';
import { createFileRoute } from '@tanstack/react-router'
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import UserAuthenticationLogs from '@/components/admin/users/auth_logs';
import UserManagement from '@/components/admin/users/management';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';

export const Route = createFileRoute('/_authenticated/admin/users/$id')({
  component: RouteComponent,
  loader: async ({ params }) => {
    const { id } = params;
    if (!id) {
      throw new Error('User ID is required');
    }

    return await getUser(id);
  }
})

function RouteComponent() {
  const data = Route.useLoaderData();
  const { t } = useTranslation();
  const [user, setUser] = useState<User>(data);

  const userFullName = (user.firstname || user.lastname) ? `${user.firstname} ${user.lastname}` : user.username;
  useEffect(() => setUser(data), [data]);

  useTranslatedTitle('admin.users.title_edit', 'admin.title', { username: userFullName});


  return <div className="pageContainer">
    <Title noMargin>
      {userFullName}
    </Title>

    <Card className='w-full sm:w-150'>
      <Title className='text-center' level={2} noMargin>{t('generic.edit')}</Title>
      <UserEditForm user={user} onSuccess={async user => setUser(user)} />
    </Card>

    <Card className='w-full sm:w-150 flex flex-col gap-4'>
      <Title className='text-center' level={2} noMargin>{t('admin.users.management.title')}</Title>
      <UserManagement user={user} onUpdated={setUser}/>
    </Card>

    <Card className='w-full sm:w-150 flex flex-col gap-4 max-h-120 overflow-auto'>
      <Title className='text-center' level={2} noMargin>{t('admin.users.auth_log.title')}</Title>
      <UserAuthenticationLogs user={user} />
    </Card>
  </div>
}
