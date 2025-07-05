import Card from '@/components/generic/card'
import Title from '@/components/generic/title'
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { createFileRoute, Link } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/admin/')({
  component: RouteComponent,
})

function RouteComponent() {
  useTranslatedTitle('admin.title');

  const { t } = useTranslation();

  return <Card className='flex flex-col gap-2 mt-4 m-auto w-full sm:w-lg items-center'>
    <Title noMargin>{t('admin.title')}</Title>
    <Link to="/admin/users">{t('admin.users.title')}</Link>
    <Link to="/admin/backdrop-albums">{t('admin.backdrop_albums.title')}</Link>
  </Card>
}
