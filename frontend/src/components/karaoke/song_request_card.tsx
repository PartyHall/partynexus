import { useTranslation } from "react-i18next";
import type { SongRequest } from "@/types/karaoke";
import Username from "../username";
import { Tooltip } from "../generic/tooltip";
import { IconCheck } from "@tabler/icons-react";
import { useAuthStore } from "@/stores/auth";
import Button from "../generic/button";
import { useState } from "react";
import { enqueueSnackbar } from "notistack";
import { deleteSongRequest } from "@/api/karaoke/requests";
import Card from "../generic/card";

type Props = {
  song: SongRequest;
  doInvalidate?: () => void;
};

export default function SongRequestCard({ song, doInvalidate }: Props) {
  const { t } = useTranslation();
  const { isGranted } = useAuthStore();

  const [isMarkingAsDone, setIsMarkingAsDone] = useState(false);

  const markAsDone = async () => {
    setIsMarkingAsDone(true);

    try {
      await deleteSongRequest(song.id);
      enqueueSnackbar(t("karaoke.request_song.request_completed"), {
        variant: "success",
      });
      doInvalidate?.();
    } catch (error) {
      console.error(error);
      enqueueSnackbar(t("generic.error.generic"), { variant: "error" });
    }

    setIsMarkingAsDone(false);
  };

  return (
    <Card className="songCard" noGlow>
      <div className="songDetails">
        <h3>
          {t("karaoke.song_title")}: {song.title}
        </h3>
        <p className="text-gray-500">
          {t("karaoke.song_artist")}: {song.artist}
        </p>
        <Username
          user={song.requestedBy}
          prefix={
            <span className="text-gray-500 text-shadow-none">
              {t("karaoke.request_song.requested_by")}:
            </span>
          }
        />
      </div>
      {isGranted("ROLE_ADMIN") && (
        <div className="songFiles pr-2">
          <Tooltip content={t("karaoke.request_song.mark_done")}>
            <Button disabled={isMarkingAsDone} onClick={markAsDone}>
              <IconCheck size={20} />
            </Button>
          </Tooltip>
        </div>
      )}
    </Card>
  );
}
