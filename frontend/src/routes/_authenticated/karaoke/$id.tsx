import { getSong } from "@/api/karaoke";
import SongEditor from "@/components/karaoke/song_editor";
import useTranslatedTitle from "@/hooks/useTranslatedTitle";
import { useSuspenseQuery } from "@tanstack/react-query";
import { createFileRoute } from "@tanstack/react-router";
import { useRouter } from "@tanstack/react-router";

/** @TODO: Before load => isGranted(ROLE_ADMIN) || 403 */

export const Route = createFileRoute("/_authenticated/karaoke/$id")({
  loader: async ({ context, params }) => {
    const id = params.id;
    if (!id) {
      throw new Error("Appliance ID is required");
    }

    // @ts-ignore
    return await context.queryClient.ensureQueryData({
      queryKey: ["karaoke_song", id],
      queryFn: async () => await getSong(id),
    });
  },
  component: RouteComponent,
});

function RouteComponent() {
  const router = useRouter();
  const id = Route.useParams().id;

  const { data, refetch } = useSuspenseQuery({
    queryKey: ["karaoke_song", id],
    queryFn: async () => await getSong(id),
  });

  useTranslatedTitle("karaoke.editor.title_edit", undefined, {
    name: data.title || "",
  });

  return (
    <SongEditor
      song={data}
      onSuccess={async () => {
        await router.invalidate();
        refetch();
      }}
    />
  );
}
