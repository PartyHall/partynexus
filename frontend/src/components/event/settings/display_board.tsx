import createDisplayBoardLink from "@/api/photobooth/display_board";
import Button from "@/components/generic/button";
import Title from "@/components/generic/title";
import { useEvent } from "@/stores/event";
import { enqueueSnackbar } from "notistack";
import { useState } from "react";
import { useTranslation } from "react-i18next";

export default function SettingsDisplayBoard() {
  const { t } = useTranslation();
  const event = useEvent();

  const [creating, setCreating] = useState<boolean>(false);
  const [key, setKey] = useState<string | null>(null);

  const createDisplayBoard = async () => {
    setCreating(true);

    try {
      const data = await createDisplayBoardLink(event.id);
      setKey(data.url);
    } catch (e) {
      console.error(e);
      enqueueSnackbar(t("generic.error.generic"), { variant: "error" });
    }

    setCreating(false);
  };

  return (
    <div className="flex flex-col gap-2">
      <Title level={2} noMargin>
        {t("events.settings.display_board.title")}
      </Title>
      <p>{t("events.settings.display_board.desc")}</p>
      {(event.displayBoardKey || key) && (
        <div className="mt-2 flex items-center justify-center">
          <a
            href={event.displayBoardKey?.url ?? key!}
            target="_blank"
            rel="noreferrer"
          >
            {t("events.settings.display_board.link")}
          </a>
        </div>
      )}

      {!event.displayBoardKey && !key && (
        <>
          <p>{t("events.settings.display_board.no_key")}</p>
          <Button disabled={creating} onClick={createDisplayBoard}>
            {t("events.settings.display_board.generate")}
          </Button>
        </>
      )}
    </div>
  );
}
