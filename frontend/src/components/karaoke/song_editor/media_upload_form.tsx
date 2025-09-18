import type { Song } from "@/types/karaoke";
import MediaList from "./media_list";
import Title from "@/components/generic/title";
import { useQueryClient } from "@tanstack/react-query";

type Props = {
  song: Song;
  onSuccess?: (song: Song) => void;
};

export default function MediaUploadForm({ song, onSuccess }: Props) {
  const qc = useQueryClient();

  const invalidateSong = () => {
    qc.removeQueries({ queryKey: ["karaoke_song", song.id] });
    onSuccess?.(song);
  };

  return (
    <div className="flex flex-col gap-4 w-full sm:w-80">
      <Title level={2} className="blue-glow text-center" noMargin>
        Song files
      </Title>

      {song.format === "/api/song_formats/video" && (
        <MediaList
          song={song}
          type="VIDEO"
          hasAnUpload={!!song.instrumentalUrl}
          fileUrl={song.instrumentalUrl}
          onMediaUploaded={invalidateSong}
        />
      )}

      {song.format === "/api/song_formats/transparent_video" && (
        <MediaList
          song={song}
          type="TRANSPARENT_VIDEO"
          hasAnUpload={!!song.instrumentalUrl}
          fileUrl={song.instrumentalUrl}
          onMediaUploaded={invalidateSong}
        />
      )}

      {song.format === "/api/song_formats/cdg" && (
        <>
          <MediaList
            song={song}
            type="INSTRUMENTAL"
            hasAnUpload={song.cdgFileUploaded}
            fileUrl={song.instrumentalUrl}
            onMediaUploaded={invalidateSong}
          />

          <MediaList
            song={song}
            type="CDG"
            hasAnUpload={song.cdgFileUploaded}
            fileUrl={null}
            onMediaUploaded={invalidateSong}
          />
        </>
      )}

      <MediaList
        song={song}
        type={"VOCALS"}
        hasAnUpload={!!song.vocalsUrl}
        fileUrl={song.vocalsUrl}
        onMediaUploaded={invalidateSong}
      />

      <MediaList
        song={song}
        type={"FULL"}
        hasAnUpload={!!song.combinedUrl}
        fileUrl={song.combinedUrl}
        onMediaUploaded={invalidateSong}
      />
    </div>
  );
}
