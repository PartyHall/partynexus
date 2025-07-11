import { deleteBackdropAlbum, getBackdropAlbum } from '@/api/photobooth/backdrops';
import BackdropCard from '@/components/admin/backdrops/backdrop_card';
import BackdropAlbumForm from '@/components/admin/backdrops/form';
import Button from '@/components/generic/button';
import Card from '@/components/generic/card';
import ConfirmButton from '@/components/generic/confirm_button';
import Title from '@/components/generic/title';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { IconPlus, IconTrash, IconUpload } from '@tabler/icons-react';
import { createFileRoute, useRouter } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute(
  '/_authenticated/admin/backdrop-albums/$id',
)({
  component: RouteComponent,
  loader: async ({ params }) => {
    const { id } = params;
    if (!id) {
      throw new Error('Backdrop album ID is required');
    }

    return await getBackdropAlbum(id);
  }
})

function RouteComponent() {
  const { t } = useTranslation();
  const navigate = Route.useNavigate();
  const { invalidate } = useRouter();
  const album = Route.useLoaderData();

  useTranslatedTitle('admin.backdrop_albums.title_edit', 'admin.title', { name: album.title });

  return <div className='flex flex-col items-center justify-center w-full mx-auto'>
    <Card className='m-auto mt-4 w-full sm:w-150'>
      <Title className='text-center' noMargin>{t('admin.backdrop_albums.title_edit', { name: album.title })}</Title>

      <BackdropAlbumForm album={album} />

      <div className='flex items-center justify-center mt-4'>
        <ConfirmButton
          variant="danger"
          tTitle='admin.backdrop_albums.delete_confirm.title'
          tConfirmButtonText='generic.delete'
          tDescription='admin.backdrop_albums.delete_confirm.desc'
          tDescriptionArgs={{ title: album.title }}
          onConfirm={async () => await deleteBackdropAlbum(album.id)}
          onSuccess={() => navigate({ to: '/admin/backdrop-albums' })}
        >
          <IconTrash size={18} />{t('admin.backdrop_albums.delete_album')}
        </ConfirmButton>
      </div>
    </Card>

    <Card className='m-auto mt-4 w-full sm:w-150'>
      <Title level={2}>{t('admin.backdrop_albums.backdrops.title')}</Title>

      <div className='flex flex-row items-center justify-center'>
        <Button>
          <IconUpload size={18} />{t('generic.add')}
        </Button>
      </div>

      {album.backdrops.map(x => <BackdropCard key={x.id} albumId={album.id} backdrop={x} invalidate={() => invalidate()} />)}

      {
        album.backdrops.length === 0
        && <p className='text-center text-primary-100 mt-4'>{t('admin.backdrop_albums.no_backdrops')}</p>
      }
    </Card>
  </div>
}
