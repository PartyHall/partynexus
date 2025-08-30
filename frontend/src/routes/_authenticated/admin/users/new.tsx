import UserEditForm from '@/components/account/user_form'
import Card from '@/components/generic/card'
import Title from '@/components/generic/title';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { createFileRoute, useNavigate } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/admin/users/new')({
  component: RouteComponent,
})

function RouteComponent() {
  const {t} = useTranslation();
  const navigate = useNavigate();

  useTranslatedTitle('admin.users.create.title', 'admin.title');

  return <Card className='pageContainer mt-4 gap-2!'>
    <Title noMargin>{t('admin.users.create.title')}</Title>
    <UserEditForm
      user={null}
      onSuccess={u => navigate({
        to: '/admin/users/$id',
        params: { id: ''+u.id },
        replace: true,
      })}
    />
  </Card>;
}
