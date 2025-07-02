import { getSongCollection } from '@/api/karaoke';
import Button, { ButtonLink } from '@/components/generic/button';
import EnumCheckboxes from '@/components/generic/enum_checkboxes';
import Input from '@/components/generic/input';
import Modal from '@/components/generic/modal';
import TriStateSwitch from '@/components/generic/multiswitch';
import Switch from '@/components/generic/switch';
import { Tooltip } from '@/components/generic/tooltip';
import SongCard from '@/components/karaoke/song_card';
import { useDebounce } from '@/hooks/useDebounce';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { useAuthStore } from '@/stores/auth';
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

type Filters = {
  modalOpen: boolean;
  ready: boolean;
  hasVocals: boolean | null;
  format: string[];
};

export const Route = createFileRoute('/_authenticated/karaoke/')({
  component: RouteComponent,
})

function RouteComponent() {
  useTranslatedTitle('karaoke.title');
  const { t } = useTranslation();
  const { isGranted } = useAuthStore();
  const { ref, inView } = useInView();

  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 500);

  const [totalSongs, setTotalSongs] = useState(-1);

  const [filters, setFilters] = useState<Filters>({
    modalOpen: false,
    ready: true,
    hasVocals: null,
    format: [],
  });

  const {
    status,
    data,
    error,
    isFetchingNextPage,
    fetchNextPage,
  } = useInfiniteQuery({
    queryKey: ['karaoke-songs', debouncedSearch, filters.ready, filters.hasVocals, filters.format],
    queryFn: async ({ pageParam = 0 }) => {
      const data = await getSongCollection({
        ready: filters.ready,
        pageParam,
        search: debouncedSearch,
        hasVocals: filters.hasVocals,
        format: filters.format,
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
        <Button onClick={() => setFilters({ ...filters, modalOpen: true })}>
          <IconFilter size={18} />
        </Button>
      </Tooltip>

      <Modal
        open={filters.modalOpen}
        onOpenChange={modalOpen => setFilters({ ...filters, modalOpen })}
        title={t('generic.filters')}
        actions={<>
          <Button onClick={() => setFilters({ ...filters, modalOpen: false })} className='px-4!'>
            {t('generic.ok')}
          </Button>
        </>}
      >
        <div className='flex flex-col gap-2'>
          <div className='flex flex-col gap-2 w-full'>
            {
              isGranted('ROLE_ADMIN')
              && <div className='flex flex-row align-center justify-between'>
                <Switch
                  id={'karaoke_filter_ready'}
                  label={t('karaoke.filters.ready')}
                  checked={filters.ready}
                  onChange={x => setFilters({ ...filters, ready: x })}
                />
              </div>
            }
            <div className='flex flex-row align-center justify-between'>
              <TriStateSwitch
                id={'karaoke_filter_vocals'}
                label={t('karaoke.filters.has_vocals')}
                value={filters.hasVocals}
                onChange={x => setFilters({ ...filters, hasVocals: x })}
              />
            </div>
            <div className='flex flex-row align-center justify-between'>
              <span className='text-sm'>{t('karaoke.filters.format')}</span>
              <EnumCheckboxes
                enumName={'song_formats'}
                align='right'
                onChange={values => setFilters({ ...filters, format: values })}
                defaultValues={filters.format}
              />
            </div>
          </div>
        </div>
      </Modal>
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
        {
          isGranted('ROLE_ADMIN')
          && <ButtonLink to='/karaoke/new'>
            <IconPlus size={19} />
            {t('karaoke.editor.title_new')}
          </ButtonLink>
        }
        <ButtonLink to='/karaoke/request'>
          <IconZoomQuestion size={19} />
          {t('karaoke.request_song.title')}
        </ButtonLink>
      </div>
    </div>
  </div>
}
