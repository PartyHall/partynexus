import { getBackdropAlbums } from "@/api/photobooth/backdrops";
import { ButtonLink } from "@/components/generic/button";
import { CardLink } from "@/components/generic/card";
import InfiniteScroll from "@/components/generic/infinite_scroll";
import useTranslatedTitle from "@/hooks/useTranslatedTitle";
import { IconPlus } from "@tabler/icons-react";
import { createFileRoute } from "@tanstack/react-router";
import { useTranslation } from "react-i18next";

export const Route = createFileRoute("/_authenticated/admin/backdrop-albums/")({
  component: RouteComponent,
});

function RouteComponent() {
  useTranslatedTitle("admin.backdrop_albums.title", "admin.title");
  const { t } = useTranslation();

  return (
    <InfiniteScroll
      title={t("admin.backdrop_albums.title")}
      fetchData={async (params) => await getBackdropAlbums(params)}
      bottomButtons={[
        <ButtonLink to="/admin/backdrop-albums/new">
          <IconPlus size={18} />
          {t("generic.create")}
        </ButtonLink>,
      ]}
      renderItem={(item) => (
        <CardLink
          to="/admin/backdrop-albums/$id"
          params={{ id: "" + item.id }}
          noGlow
        >
          <h3 className="text-lg">{item.title}</h3>
          <p className="text-sm text-gray-500">
            {t("admin.backdrop_albums.album_version", {
              version: item.version,
            })}
          </p>
          <p className="text-sm text-gray-500">{item.author}</p>
        </CardLink>
      )}
      queryKey={["backdrop_albums"]}
      totalTranslationKey="admin.backdrop_albums.amt_album"
    />
  );
}
