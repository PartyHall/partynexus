import { getSongCollection } from '@/api/karaoke';
import Button from '@/components/generic/button';
import Input from '@/components/generic/input';
import { Tooltip } from '@/components/generic/tooltip';
import SongCard from '@/components/karaoke/song_card';
import { useDebounce } from '@/hooks/useDebounce';
import { IconFilter, IconPlus, IconSearch, IconX, IconZoomQuestion } from '@tabler/icons-react';
import { useInfiniteQuery } from '@tanstack/react-query';
import { createFileRoute } from '@tanstack/react-router';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useInView } from 'react-intersection-observer';

/**
 * @TODO:
 * Implement @tanstack/virtual
 */

export const Route = createFileRoute('/_authenticated/karaoke/')({
  component: RouteComponent,
})

function RouteComponent() {
  const { t } = useTranslation();
  const { ref, inView } = useInView();

  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 500);

  const [totalSongs, setTotalSongs] = useState(-1);

  const {
    status,
    data,
    error,
    isFetchingNextPage,
    fetchNextPage,
  } = useInfiniteQuery({
    queryKey: ['karaoke-songs', debouncedSearch],
    queryFn: async ({ pageParam = 0 }) => {
      const data = await getSongCollection({
        ready: true,
        pageParam,
        search: debouncedSearch,
      });

      setTotalSongs(data.totalItems);

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

  return <div className='pageContainer sm:w-150! flex flex-col gap-4! flex-1 h-full'>
    <div className='flex flex-row w-full gap-2'>
      <Input
        placeholder={t('generic.search')}
        icon={<IconSearch />}
        value={search}
        onChange={e => setSearch(e.target.value)}
        action={<Tooltip content={t('generic.clear_search')}>
          <Button onClick={() => setSearch('')}>
            <IconX size={18} />
          </Button>
        </Tooltip>}
      />
      <Tooltip content={t('generic.filters')}>
        <Button>
          <IconFilter size={18} />
        </Button>
      </Tooltip>
    </div>
    <div className='flex-1 w-full overflow-y-auto'>
      {status === 'pending' && <div className='text-center'>{t('generic.loading')}</div>}
      {status === 'error' && <div className='text-center text-red-glow'>Error: {error.message}</div>}

      {
        (status !== 'pending' && status !== 'error')
        && <div className='flex flex-col gap-2 w-full overflow-y-auto'>
          {
            data.pages.map(page => (
              <>
                {
                  page.data.map(song => <SongCard key={song.id} song={song} />)
                }
              </>
            ))
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
    <div className='flex flex-row justify-between w-full items-center'>
      <span className='flex-1'>{t('karaoke.amt_songs', { amt: totalSongs >= 0 ? totalSongs : '?' })}</span>
      <div className='flex-end flex gap-2'>
        <Button>
          <IconPlus size={19} />
          {t('karaoke.add_song.title')}
        </Button>
        <Button>
          <IconZoomQuestion size={19} />
          {t('karaoke.request_song.title')}
        </Button>
      </div>
    </div>
  </div>
}
