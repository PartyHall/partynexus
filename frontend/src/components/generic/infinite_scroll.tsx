import { useEffect, useState, type ReactNode } from "react";
import Title from "./title";
import { useTranslation } from "react-i18next";
import { useInView } from "react-intersection-observer";
import { useInfiniteQuery } from "@tanstack/react-query";
import type { Collection } from "@/types";

/**
 * @TODO:
 * Implement @tanstack/virtual
 */

type Props<T> = {
  title?: string;
  searchComponent?: ReactNode;
  renderItem: (item: T) => ReactNode;
  totalTranslationKey?: string;
  bottomButtons?: ReactNode[];
  fetchData: (params: any) => Promise<Collection<T>>;
  queryKey: any[];
};

export default function InfiniteScroll<T>({
  title,
  searchComponent,
  renderItem,
  totalTranslationKey,
  bottomButtons,
  fetchData,
  queryKey,
}: Props<T>) {
  const { t } = useTranslation();

  const { ref, inView } = useInView();

  const [totalItems, setTotalItems] = useState(-1);

  const { status, data, error, isFetchingNextPage, fetchNextPage } =
    useInfiniteQuery({
      queryKey: queryKey,
      queryFn: async (params) => {
        const data = await fetchData(params);

        setTotalItems(data.totalItems);

        return {
          data: data.member,
          currentPage: params.pageParam,
          previousPage: data.view?.previous ?? null,
          nextPage: data.view?.next ?? null,
        };
      },
      initialPageParam: 1,
      getPreviousPageParam: (firstPage) => firstPage.nextPage,
      getNextPageParam: (lastPage) => lastPage.nextPage,
    });

  useEffect(() => {
    if (inView) {
      fetchNextPage();
    }
  }, [fetchNextPage, inView]);

  return (
    <div className="pageContainer sm:w-150! flex flex-col gap-4! flex-1 h-full">
      {title && <Title noMargin>{title}</Title>}
      {searchComponent}

      <div className="flex-1 w-full overflow-y-auto">
        {status === "pending" && (
          <div className="text-center">{t("generic.loading")}</div>
        )}
        {status === "error" && (
          <div className="text-center text-red-glow">
            Error: {error.message}
          </div>
        )}

        {status !== "pending" && status !== "error" && (
          <div className="flex flex-col gap-2 w-full overflow-y-auto">
            {data.pages.map((page) => page.data.map(renderItem))}
            <div
              ref={ref}
              className="h-8 pt-2 w-full text-center text-red-glow"
            >
              {isFetchingNextPage
                ? t("generic.loading")
                : t("generic.no_more_results")}
            </div>
          </div>
        )}
      </div>
      <div className="flex flex-row justify-between w-full items-center">
        {totalTranslationKey && (
          <span className="flex-1">
            {t(totalTranslationKey, {
              amt: totalItems >= 0 ? totalItems : "?",
            })}
          </span>
        )}
        <div className="flex-end flex gap-2">{bottomButtons}</div>
      </div>
    </div>
  );
}
