import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { t } from "i18next";
import Title from "@/components/generic/title";
import { useEffect } from "react";
import { useEvent } from "@/stores/event";

export const Route = createFileRoute("/_authenticated/$id/timelapse")({
  component: RouteComponent,
});

function RouteComponent() {
  const navigate = useNavigate();
  const event = useEvent();

  useEffect(() => {
    if (
      !event.export ||
      event.export.status.value !== "complete" ||
      !event.export.timelapse
    ) {
      navigate({ to: "/$id", params: { id: event.id }, replace: true });
    }
  }, [event]);

  return (
    <div className="flex flex-col gap-2">
      <Title>{t("events.timelapse")}</Title>

      {!event.export?.timelapse && <p>{t("events.no_timelapse")}</p>}

      {/*
    @TODO: Do something for it to fucking works everytime
    a lot of the time it just get 401 or stuff like that
    maybe we should use fetch+blob to ensure everything is OK
    idk
  */}

      {event.export?.timelapse && (
        <video className="w-full" controls>
          <source src={`/api/events/${event.id}/timelapse`} type="video/mp4" />
          {t("events.no_timelapse")}
        </video>
      )}
    </div>
  );
}
