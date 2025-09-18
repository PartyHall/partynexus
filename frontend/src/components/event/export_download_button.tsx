import type { Event } from "@/types/event";
import Button from "../generic/button";
import { useTranslation } from "react-i18next";
import { IconDownload } from "@tabler/icons-react";

type Props = {
  event: Event;
  className?: string;
};

export default function ExportDownloadButton({ event, className }: Props) {
  const { t } = useTranslation();
  const fullClassName = `w-full ${className ?? ""}`;

  const onDownload = () => {
    const link = document.createElement("a");
    link.href = `/api/events/${event.id}/export`;
    link.download = `${event.id}.zip`;
    document.body.appendChild(link);
    link.click();
    link.remove();
  };

  return (
    <Button className={fullClassName} onClick={onDownload}>
      <IconDownload size={18} />
      {t("events.download_everything")}
    </Button>
  );
}
