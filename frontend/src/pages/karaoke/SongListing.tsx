import {
    Button,
    Checkbox,
    Flex,
    Input,
    Pagination,
    Popover,
    Segmented,
    Switch,
    Typography,
} from 'antd';
import {
    IconFilter,
    IconSquareRoundedPlus,
    IconZoomQuestion,
} from '@tabler/icons-react';
import PnSong, { SongFormat } from '../../sdk/responses/song';
import { useAsyncEffect, useDebounce, useTitle } from 'ahooks';
import { useNavigate, useSearchParams } from 'react-router-dom';

import { CheckboxChangeEvent } from 'antd/es/checkbox';
import { Collection } from '../../sdk/responses/collection';
import Loader from '../../components/Loader';
import SongCard from '../../components/SongCard';

import { useAuth } from '../../hooks/auth';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Context = {
    loading: boolean;
    songs: Collection<PnSong> | null;
    search: string;
    page: number;

    ready: boolean;
    format: SongFormat[];
    hasVocals: boolean | null;
};

/**
 * @TODO: Use SearchablePaginatedList
 */

export default function SongListingPage() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const { api, isGranted } = useAuth();
    const [searchParams, setSearchParams] = useSearchParams();
    const [notif, notifHandler] = useNotification();

    const [ctx, setCtx] = useState<Context>({
        loading: true,
        songs: null,
        search: searchParams.get('search') ?? '',
        page: parseInt(searchParams.get('page') || '1'),

        ready: true,
        format: (searchParams.get('format')?.split(',') as SongFormat[]) ?? [],
        hasVocals: searchParams.has('has_vocals')
            ? searchParams.get('has_vocals') === 'true'
            : null,
    });

    const debouncedSearch = useDebounce(ctx.search, {
        wait: 500,
    });

    useTitle(t('karaoke.title') + ' - PartyHall');

    useAsyncEffect(async () => {
        const params: any = { page: ctx.page + '' };
        if (ctx.search) {
            params['search'] = ctx.search;
        }

        if (!ctx.ready) {
            params['ready'] = false;
        }

        if (ctx.format.length > 0) {
            params['format'] = ctx.format.join(',');
        }

        if (ctx.hasVocals !== null) {
            params['has_vocals'] = ctx.hasVocals;
        }

        setSearchParams(params);

        setCtx((oldCtx) => ({ ...oldCtx, loading: true }));

        try {
            const songs = await api.karaoke.getCollection(
                ctx.page,
                debouncedSearch,
                ctx.ready,
                ctx.hasVocals,
                ctx.format
            );

            setCtx((oldCtx) => ({
                ...oldCtx,
                loading: false,
                songs,
            }));
        } catch (e) {
            console.error(e);
            notif.error({
                message: t('generic.error.unknown'),
                description: t('generic.error.unknown_desc'),
            });
        }
    }, [debouncedSearch, ctx.page, ctx.ready, ctx.hasVocals, ctx.format]);

    const onFormatChange = (x: CheckboxChangeEvent) => {
        if (!x.target.checked) {
            setCtx((oldCtx) => ({
                ...oldCtx,
                page: 1,
                format: oldCtx.format.filter((y) => y !== x.target.value),
            }));

            return;
        }

        if (ctx.format.includes(x.target.value)) {
            return;
        }

        setCtx((oldCtx) => ({
            ...oldCtx,
            page: 1,
            format: [...oldCtx.format, x.target.value],
        }));
    };

    return (
        <>
            <Flex vertical style={{ height: '100%' }} gap={8}>
                <Flex gap={8}>
                    <Input
                        placeholder={t('generic.search')}
                        value={ctx.search}
                        onChange={(x) =>
                            setCtx((old) => ({
                                ...old,
                                search: x.target.value,
                                page: 1,
                            }))
                        }
                    />

                    <Popover
                        title={t('karaoke.filters.title')}
                        content={
                            <Flex vertical gap={4}>
                                {isGranted('ROLE_ADMIN') && (
                                    <Flex gap={3} align="center">
                                        <Typography style={{ flex: 1 }}>
                                            {t('karaoke.filters.ready')}
                                        </Typography>
                                        <Switch
                                            value={ctx.ready}
                                            onChange={(x) =>
                                                setCtx((old) => ({
                                                    ...old,
                                                    ready: x.valueOf(),
                                                    page: 1,
                                                }))
                                            }
                                        />
                                    </Flex>
                                )}
                                <Flex gap={3} align="center">
                                    <Typography style={{ flex: 1 }}>
                                        {t('karaoke.filters.has_vocals')}
                                    </Typography>
                                    <Segmented<string>
                                        options={[
                                            t('karaoke.filters.no'),
                                            '?',
                                            t('karaoke.filters.yes'),
                                        ]}
                                        onChange={(val) => {
                                            setCtx((oldCtx) => ({
                                                ...oldCtx,
                                                hasVocals:
                                                    val == '?'
                                                        ? null
                                                        : val ===
                                                          t(
                                                              'karaoke.filters.yes'
                                                          ),
                                                page: 1,
                                            }));
                                        }}
                                    />
                                </Flex>
                                <Typography style={{ flex: 1 }}>
                                    {t('karaoke.filters.format')}:
                                </Typography>
                                <Checkbox
                                    value="video"
                                    checked={ctx.format.includes('video')}
                                    onChange={onFormatChange}
                                >
                                    {t('karaoke.filters.video')}
                                </Checkbox>

                                <Checkbox
                                    value="cdg"
                                    checked={ctx.format.includes('cdg')}
                                    onChange={onFormatChange}
                                >
                                    {t('karaoke.filters.cdg')}
                                </Checkbox>
                                <Checkbox
                                    value="transparent_video"
                                    checked={ctx.format.includes(
                                        'transparent_video'
                                    )}
                                    onChange={onFormatChange}
                                >
                                    {t('karaoke.filters.transparent_video')}
                                </Checkbox>
                            </Flex>
                        }
                        trigger="click"
                    >
                        <Button icon={<IconFilter size={20} />} />
                    </Popover>
                </Flex>

                <Flex
                    vertical
                    gap={16}
                    align="stretch"
                    style={{ overflowY: 'auto', flex: '100%' }}
                >
                    <span></span>{' '}
                    {/* Bypass the :empty of antd, will be fixed once we go on SearchablePaginatedList*/}
                    <Loader loading={ctx.loading}>
                        {ctx.songs?.items.map((x) => (
                            <SongCard key={x.iri} song={x} />
                        ))}
                    </Loader>
                </Flex>

                <Flex
                    className="SongListing__pagination"
                    align="center"
                    justify="space-between"
                    wrap="wrap"
                >
                    <Pagination
                        align="center"
                        total={ctx.songs?.total ?? 10}
                        pageSize={30} // @TODO: Default API platform one but we should add it to the hydra thing so that the front knows it
                        showTotal={(total) =>
                            t('karaoke.song_count', { total })
                        }
                        showSizeChanger={false}
                        current={ctx.page}
                        onChange={(x) =>
                            setCtx((old) => ({ ...old, page: x.valueOf() }))
                        }
                    />

                    <Button
                        onClick={() => navigate('/karaoke/request')}
                        icon={<IconZoomQuestion size={20} />}
                    >
                        {t('karaoke.request.title')}
                    </Button>
                    {isGranted('ROLE_ADMIN') && (
                        <Button
                            onClick={() => navigate('/karaoke/new')}
                            icon={<IconSquareRoundedPlus size={20} />}
                        >
                            {t('karaoke.new_song')}
                        </Button>
                    )}
                </Flex>
            </Flex>

            {notifHandler}
        </>
    );
}
