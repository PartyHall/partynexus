import { Button, Flex, Input, Pagination, Switch, Typography } from 'antd';
import { IconSquareRoundedPlus, IconZoomQuestion } from '@tabler/icons-react';
import { useAsyncEffect, useDebounce, useTitle } from 'ahooks';
import { useNavigate, useSearchParams } from 'react-router-dom';

import { Collection } from '../../sdk/responses/collection';
import Loader from '../../components/Loader';
import PnSong from '../../sdk/responses/song';
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

        setSearchParams(params);

        setCtx((oldCtx) => ({ ...oldCtx, loading: true }));

        try {
            const songs = await api.karaoke.getCollection(
                ctx.page,
                debouncedSearch,
                ctx.ready
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
    }, [debouncedSearch, ctx.page, ctx.ready]);

    return (
        <>
            <Flex vertical style={{ height: '100%' }} gap={8}>
                <Flex gap={8}>
                    <Input
                        placeholder={t('karaoke.search')}
                        value={ctx.search}
                        onChange={(x) =>
                            setCtx((old) => ({
                                ...old,
                                search: x.target.value,
                                page: 1,
                            }))
                        }
                    />

                    {isGranted('ROLE_ADMIN') && (
                        <Flex gap={3} align="center">
                            <Typography style={{ width: 50 }}>
                                Ready?
                            </Typography>
                            <Switch
                                value={ctx.ready}
                                onChange={(x) =>
                                    setCtx((old) => ({
                                        ...old,
                                        ready: x.valueOf(),
                                    }))
                                }
                            />
                        </Flex>
                    )}
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
                        {t('karaoke.request_song')}
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
