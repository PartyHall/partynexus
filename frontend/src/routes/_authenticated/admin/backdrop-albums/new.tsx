import BackdropAlbumForm from "@/components/admin/backdrops/album_form";
import Card from "@/components/generic/card";
import Title from "@/components/generic/title";
import useTranslatedTitle from "@/hooks/useTranslatedTitle";
import { createFileRoute } from "@tanstack/react-router";
import { useTranslation } from "react-i18next";

export const Route = createFileRoute(
  "/_authenticated/admin/backdrop-albums/new",
)({
  component: RouteComponent,
});

function RouteComponent() {
  const { t } = useTranslation();
  const navigate = Route.useNavigate();

  useTranslatedTitle("admin.backdrop_albums.title_new", "admin.title");

  return (
    <div className="flex flex-col items-center justify-center w-full mx-auto">
      <Card className="m-auto mt-4 w-full sm:w-150">
        <Title className="text-center" noMargin>
          {t("admin.backdrop_albums.title_new")}
        </Title>

        <BackdropAlbumForm
          album={null}
          onSuccess={(album) =>
            navigate({
              to: "/admin/backdrop-albums/$id",
              params: { id: "" + album.id },
              replace: true,
            })
          }
        />
      </Card>
    </div>
  );
}
