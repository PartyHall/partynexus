import { customFetch } from "@/api/customFetch";
import Input from "@/components/generic/input";
import Modal from "@/components/generic/modal";
import { useDebounce } from "@/hooks/useDebounce";
import type { ExternalSong } from "@/types/karaoke";
import { enqueueSnackbar } from "notistack";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import ExternalSongCard from "../external_song_card";

type Props = {
  service: "spotify" | "musicBrainz";
  defaultArtist?: string | null;
  defaultTitle?: string | null;

  open: boolean;
  onClose: (id: ExternalSong | null) => void;
};

export default function ExternalSongModal({
  service,
  defaultArtist,
  defaultTitle,
  open,
  onClose,
}: Props) {
  const { t } = useTranslation();

  const [results, setResults] = useState<ExternalSong[]>([]);

  const [title, setTitle] = useState(defaultTitle || "");
  const [artist, setArtist] = useState(defaultArtist || "");

  const debouncedTitle = useDebounce(title, 500);
  const debouncedArtist = useDebounce(artist, 500);

  useEffect(() => {
    (async () => {
      if (!debouncedTitle || !debouncedArtist) {
        return;
      }

      try {
        const resp = await customFetch(
          `/api/external/${service.toLowerCase()}/${debouncedArtist}/${debouncedTitle}`,
        );
        const data = await resp.json();

        setResults(data["member"]);
      } catch (err) {
        console.error("Error fetching external song data:", err);
        setResults([]);
        enqueueSnackbar(t("generic.error.generic"), { variant: "error" });
      }
    })();
  }, [title, artist]);

  useEffect(() => {
    setTitle(defaultTitle || "");
    setArtist(defaultArtist || "");
    setResults([]);
  }, [defaultTitle, defaultArtist]);

  return (
    <Modal
      open={open}
      onOpenChange={() => onClose(null)}
      title={t(`karaoke.editor.external_song.title_${service}`)}
      description={t(`karaoke.editor.external_song.title_${service}`)}
    >
      <Input
        label={t("karaoke.song_title")}
        value={title}
        onChange={(x) => setTitle(x.target.value)}
      />
      <Input
        label={t("karaoke.song_artist")}
        value={artist}
        onChange={(x) => setArtist(x.target.value)}
      />

      <div className="overflow-y-auto">
        {results.map((x) => (
          <ExternalSongCard key={x.id} song={x} onSelect={() => onClose(x)} />
        ))}
      </div>
    </Modal>
  );
}
