import { Flex, Input, Pagination, Typography } from "antd";
import { MutableRefObject, ReactNode, useEffect, useState } from "react";
import { useAsyncEffect, useDebounce } from "ahooks";

import { Collection } from "../sdk/responses/collection";
import Loader from "./Loader";

import useNotification from "antd/es/notification/useNotification";
import { useSearchParams } from "react-router-dom";
import { useTranslation } from "react-i18next";

type Props<T> = {
    searchParameterName?: string | null;
    doSearch: (query: string, page: number) => Promise<Collection<T> | null>;
    renderElement: (element: T) => ReactNode;
    extraFilters?: ReactNode;
    extraActions?: ReactNode;

    noResults?: string | null;
    searchTranslationKey?: string | null;
    countTranslationKey?: string | null;

    showSearch?: boolean;

    className?: string;
    requestRefresh?: MutableRefObject<(() => Promise<void>) | undefined>;
};

type State<T> = {
    loading: boolean;
    results: Collection<T> | null;

    search: string;
    page: number;
};

export default function SearchablePaginatedList<T>(props: Props<T>) {
    const {
        searchParameterName,
        doSearch,
        renderElement,
        extraFilters,
        extraActions,
        searchTranslationKey,
        countTranslationKey,
        className,
    } = props;

    const [searchParams, setSearchParams] = useSearchParams();
    const { t } = useTranslation();
    const [notif, notifCtx] = useNotification();
    const [ctx, setCtx] = useState<State<T>>({
        loading: true,
        results: null,
        search: searchParams.get(searchParameterName ?? 'search') ?? '',
        page: parseInt(searchParams.get('page') || '1'),
    });

    const debouncedSearch = useDebounce(ctx.search, { wait: 500 });

    const classNames = className ?? '';

    const refresh = async () => {
        try {
            const results = await doSearch(debouncedSearch, ctx.page);
            setCtx(oldCtx => ({ ...oldCtx, loading: false, results }));
        } catch (e) {
            console.error(e);
            notif.error({
                message: t('generic.error.unknown'),
                description: t('generic.error.unknown_desc'),
            });
        }
    };

    useAsyncEffect(async () => {
        const params: any = { }
        if (ctx.page > 1) {
            params['page'] = ctx.page + '';
        }

        if (ctx.search) {
            params[searchParameterName ?? 'search'] = ctx.search
        }

        setSearchParams(params);

        setCtx(oldCtx => ({ ...oldCtx, loading: true }));

        await refresh();
    }, [debouncedSearch, ctx.page]);

    useEffect(() => {
        if (props.requestRefresh) {
            props.requestRefresh.current = refresh;
        }
    }, []);

    return <Flex vertical style={{ flex: '100%', overflow: 'auto' }} gap={8}>
        {
            props.showSearch === undefined || props.showSearch === true
            && <Flex gap={8}>
                <Input
                    placeholder={t(searchTranslationKey ?? 'generic.search')}
                    value={ctx.search}
                    onChange={x => setCtx(old => ({ ...old, search: x.target.value, page: 1 }))}
                />
                {extraFilters}
            </Flex>
        }

        <Flex vertical gap={16} align="stretch" style={{ overflowY: 'auto', flex: '1' }} className={classNames}>
            <Loader loading={ctx.loading}>
                {
                    ctx.results?.items.length !== 0
                    && ctx.results?.items.map(renderElement)
                }
                {
                    ctx.results?.items.length === 0
                    && <Typography.Title level={3}>{t(props.noResults ?? 'generic.no_results')}</Typography.Title>
                }
            </Loader >
        </Flex>

        <Flex align="center" justify="space-between">
            {
                ctx.results?.items.length !== 0
                && <Pagination
                    align="center"
                    total={ctx.results?.total ?? 10}
                    pageSize={30} // @TODO: Default API platform one but we should add it to the hydra thing so that the front knows it
                    showTotal={(total,) => t(countTranslationKey ?? 'generic.list_count', { total })}
                    showSizeChanger={false}
                    current={ctx.page}
                    onChange={x => setCtx(old => ({ ...old, page: x.valueOf() }))}
                />
            }
            {extraActions}
        </Flex>

        {notifCtx}
    </Flex>
}