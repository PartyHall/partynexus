import { getSongCollection } from '@/api/karaoke';
import Button, { ButtonLink } from '@/components/generic/button';
import EnumCheckboxes from '@/components/generic/enum_checkboxes';
import InfiniteScroll from '@/components/generic/infinite_scroll';
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
import { createFileRoute } from '@tanstack/react-router';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * @TODO:
 * Implement @tanstack/virtual
 * 
 * @TODO: Fix the infinite scroll filters
 */

type Filters = {
  modalOpen: boolean;
  ready: boolean;
  vocals: boolean | null;
  format: string[];
};

export const Route = createFileRoute('/_authenticated/karaoke/')({
  component: RouteComponent,
})

function RouteComponent() {
  useTranslatedTitle('karaoke.title');
  const { t } = useTranslation();
  const { isGranted } = useAuthStore();

  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 500);

  const [filters, setFilters] = useState<Filters>({
    modalOpen: false,
    ready: true,
    vocals: null,
    format: [],
  });

  return <InfiniteScroll
    fetchData={async params => await getSongCollection({ ...params, ...filters, search: debouncedSearch })}
    queryKey={['karaoke-songs', debouncedSearch, filters.ready, filters.vocals, filters.format]}
    renderItem={song => <SongCard key={song.id} song={song} />}
    totalTranslationKey='karaoke.amt_songs'
    bottomButtons={[
      <>
        {
          isGranted('ROLE_ADMIN')
          && <ButtonLink to='/karaoke/new'>
            <IconPlus size={19} />
            {t('karaoke.editor.title_new')}
          </ButtonLink>
        }
      </>,
      <ButtonLink to='/karaoke/request'>
        <IconZoomQuestion size={19} />
        {t('karaoke.request_song.title')}
      </ButtonLink>
    ]}
    searchComponent={<div className='flex flex-row w-full gap-2'>
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
                value={filters.vocals}
                onChange={x => setFilters({ ...filters, vocals: x })}
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
    </div>}
  />;
}
