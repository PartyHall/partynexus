import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute(
  '/_authenticated/admin/backdrop-albums/new',
)({
  component: RouteComponent,
})

function RouteComponent() {
  useTranslatedTitle('admin.backdrop_albums.title_new', 'admin.title');

  return <div>Hello "/_authenticated/admin/backdrop-albums/new"!</div>
}
