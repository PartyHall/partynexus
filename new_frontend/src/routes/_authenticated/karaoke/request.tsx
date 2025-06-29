import { HttpError } from '@/api/http_error';
import { createSongRequest, getSongRequests } from '@/api/karaoke/requests';
import { ValidationError } from '@/api/violations_error';
import Button from '@/components/generic/button';
import Input from '@/components/generic/input';
import Title from '@/components/generic/title';
import SongRequestCard from '@/components/karaoke/song_request_card';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { IconSend2 } from '@tabler/icons-react';
import { useInfiniteQuery } from '@tanstack/react-query';
import { createFileRoute } from '@tanstack/react-router';
import { enqueueSnackbar } from 'notistack';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { useInView } from 'react-intersection-observer';

/**
 * @TODO:
 * Implement @tanstack/virtual maybe?
 * Not sure if really required here as song request are probably way less full
 * than the song list
 */

export const Route = createFileRoute('/_authenticated/karaoke/request')({
  component: RouteComponent,
})

type SongRequestForm = {
  title: string;
  artist: string;
};

function RouteComponent() {
  useTranslatedTitle('karaoke.request_song.title')
  const { t } = useTranslation();
  const { ref, inView } = useInView();
  const [globalErrors, setGlobalErrors] = useState<string[]>([]);

  const {
    handleSubmit,
    register,
    reset,
    setError,
    formState: { isSubmitting, errors },
  } = useForm<SongRequestForm>();

  const {
    status,
    data,
    error,
    isFetchingNextPage,
    fetchNextPage,
    refetch,
  } = useInfiniteQuery({
    queryKey: ['songs-request'],
    queryFn: async ({ pageParam = 0 }) => {
      const data = await getSongRequests(pageParam);

      return {
        data: data.member,
        currentPage: pageParam,
        previousPage: data.view?.previous ?? null,
        nextPage: data.view?.next ?? null,
      };
    },
    initialPageParam: 1,
    getPreviousPageParam: firstPage => firstPage.nextPage,
    getNextPageParam: lastPage => lastPage.nextPage,
  });

  useEffect(() => {
    if (inView) {
      fetchNextPage();
    }
  }, [fetchNextPage, inView]);

  const onSubmit = async (data: SongRequestForm) => {
    setGlobalErrors([]);

    try {
      await createSongRequest(data.title, data.artist);

      reset();
      enqueueSnackbar(
        t('karaoke.request_song.success'),
        { variant: 'success' },
      );
      refetch();
    } catch (err) {
      if (err instanceof ValidationError) {
        const globalErrors = err.applyToReactHookForm(setError);
        if (globalErrors.length > 0) {
          setGlobalErrors(globalErrors);
        }

        return;
      }

      if (err instanceof HttpError) {
        enqueueSnackbar(
          t('generic.error.generic_with_message', { message: err.message }),
          { variant: 'error' },
        );
        return;
      }

      enqueueSnackbar(
        t('generic.error.generic'),
        { variant: 'error' },
      );

      console.error('Error creating song request:', err);
    }
  };

  return <div className='pageContainer sm:w-150! flex flex-col gap-4! flex-1 h-full'>
    <form
      onSubmit={handleSubmit(onSubmit)}
      className='flex flex-col gap-0.5 w-full bg-synthbg-800 p-8 rounded-2xl'
    >
      <Title noMargin>{t('karaoke.request_song.title')}</Title>

      <span className='mt-4'>{t('karaoke.song_title')}:</span>
      <Input {...register('title', { required: true })} error={errors.title} />

      <span className='mt-4'>{t('karaoke.song_artist')}:</span>
      <Input {...register('artist', { required: true })} error={errors.artist} />

      {
        globalErrors.length > 0
        && <div className="text-red-glow mb-4">
          {
            globalErrors.map((error, index) => (
              <p key={index}>{error}</p>
            ))
          }
        </div>
      }

      <Button
        type='submit'
        className='mt-5 w-full'
        disabled={isSubmitting}
      >
        <IconSend2 />
        {t('karaoke.request_song.submit')}
      </Button>
    </form>

    <div className='flex-1 w-full overflow-y-auto'>
      {status === 'pending' && <div className='text-center'>{t('generic.loading')}</div>}
      {status === 'error' && <div className='text-center text-red-glow'>Error: {error.message}</div>}

      {
        (status !== 'pending' && status !== 'error')
        && <div className='flex flex-col gap-2 w-full overflow-y-auto'>
          {
            data.pages.map(page => <>
              {page.data.map(song => <SongRequestCard key={song.id} song={song} doInvalidate={refetch} />)}
            </>)
          }

          <div ref={ref} className='h-8 pt-2 w-full text-center text-red-glow'>
            {
              isFetchingNextPage
                ? t('generic.loading')
                : t('generic.no_more_results')
            }
          </div>
        </div>
      }
    </div>
  </div>
}
