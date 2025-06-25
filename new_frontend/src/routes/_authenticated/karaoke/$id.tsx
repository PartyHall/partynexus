import { getSong } from '@/api/karaoke';
import SongEditor from '@/components/karaoke/song_editor';
import { createFileRoute } from '@tanstack/react-router'

/** @TODO: Before load => isGranted(ROLE_ADMIN) || 403 */

export const Route = createFileRoute('/_authenticated/karaoke/$id')({
  loader: async ({params}) => {
    const id = params.id;
    if (!id) {
      throw new Error('Appliance ID is required');
    }

    return await getSong(id);
  },
  component: RouteComponent,
})

function RouteComponent() {
  const data = Route.useLoaderData();

  return <SongEditor song={data} />
}
