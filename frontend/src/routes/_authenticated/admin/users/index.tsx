import { getUsers } from '@/api/users'
import UserCard from '@/components/admin/users/user_card'
import Button, { ButtonLink } from '@/components/generic/button'
import InfiniteScroll from '@/components/generic/infinite_scroll'
import Input from '@/components/generic/input'
import { Tooltip } from '@/components/generic/tooltip'
import { useDebounce } from '@/hooks/useDebounce'
import useTranslatedTitle from '@/hooks/useTranslatedTitle'
import { IconPlus, IconSearch, IconX } from '@tabler/icons-react'
import { createFileRoute } from '@tanstack/react-router'
import { useState } from 'react'
import { useTranslation } from 'react-i18next'

export const Route = createFileRoute('/_authenticated/admin/users/')({
  component: RouteComponent,
})

function RouteComponent() {
  useTranslatedTitle('admin.users.title', 'admin.title');
  const { t } = useTranslation();

  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 500);

  return <InfiniteScroll
    title={t('admin.users.title')}
    fetchData={async params => await getUsers({ ...params, search: debouncedSearch, showBanned: true })}
    queryKey={['admin-users', debouncedSearch]}
    renderItem={user => <UserCard user={user} />}
    totalTranslationKey='admin.users.amt_users'
    searchComponent={<Input
      placeholder={t('generic.search')}
      icon={<IconSearch />}
      value={search}
      onChange={e => setSearch(e.target.value)}
      action={<Tooltip content={t('generic.clear_search')}>
        <Button onClick={() => setSearch('')}>
          <IconX size={18} />
        </Button>
      </Tooltip>}
    />}
    bottomButtons={[
      <ButtonLink to='/admin/users/new'><IconPlus size={18} />{t('generic.create')}</ButtonLink>
    ]}
  />
}