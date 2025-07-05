import UserEditForm from '@/components/account/user_form'
import Card from '@/components/generic/card'
import { createFileRoute, useNavigate } from '@tanstack/react-router'

export const Route = createFileRoute('/_authenticated/admin/users/new')({
  component: RouteComponent,
})

function RouteComponent() {
  const navigate = useNavigate();

  return <Card className='pageContainer mt-4'>
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
