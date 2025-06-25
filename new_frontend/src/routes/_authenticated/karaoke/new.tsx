import SongEditor from '@/components/karaoke/song_editor'
import { createFileRoute } from '@tanstack/react-router'

/** @TODO: Before load => isGranted(ROLE_ADMIN) || 403 */
export const Route = createFileRoute('/_authenticated/karaoke/new')({
  component: RouteComponent,
})

function RouteComponent() {
  const navigate = Route.useNavigate();

  return <SongEditor onSuccess={s => navigate({
    to: `/karaoke/$id`,
    params: { id: '' + s.id },
    replace: true,
  })} />;
}
