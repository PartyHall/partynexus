import { getEventById } from "@/api/events";
import Card from "@/components/generic/card";
import Title from "@/components/generic/title";
import { useMercureListener } from "@/hooks/useMercure";
import { useNexusTitle } from "@/hooks/useTitle";
import { useAuthStore } from "@/stores/auth";
import { useEventStore } from "@/stores/event";
import {
  createFileRoute,
  Link,
  Outlet,
  useRouter,
} from "@tanstack/react-router";
import { useEffect } from "react";
import { useTranslation } from "react-i18next";

/**
 * @TODO: Add mercure here so that the event updates in real-time
 * esp. export
 */

export const Route = createFileRoute("/_authenticated/$id")({
  component: RouteComponent,
  loader: async ({ params }) => {
    if (!params.id) {
      throw new Error("Missing required parameter: id");
    }

    return await getEventById(params.id);
  },
});

type Subroute =
  | ""
  | "/photos"
  | "/timelapse"
  | "/participants"
  | "/songs"
  | "/settings";

function SubrouteLink({
  text,
  to,
  id,
}: {
  text: string;
  to: Subroute;
  id: string;
}) {
  const { t } = useTranslation();
  const route = useRouter();
  const isActive = route.state.location.pathname === `/${id}${to}`;

  return isActive ? (
    <span className="bg-synthbg-900 p-2 rounded-md">{t(text)}</span>
  ) : (
    <Link className="m-2" to={`/$id${to}`} params={{ id: id }} replace>
      {t(text)}
    </Link>
  );
}

function RouteComponent() {
  const data = Route.useLoaderData();
  const tokenUser = useAuthStore((state) => state.tokenUser);
  const { event, setEvent } = useEventStore();

  useNexusTitle(data.name);
  useMercureListener(`/events/${data.id}`, (event) => setEvent(event));

  useEffect(() => setEvent(data), [data]);

  if (!event) {
    return;
  }

  return (
    <div className="flex flex-col p-4 mx-auto items-center gap-3 w-full sm:w-150 h-[100%]">
      <Card className="w-full">
        <Title noMargin className="text-center mb-2">
          {event.name}
        </Title>

        <div className="w-full flex flex-row flex-wrap items-center justify-around p-2 bg-synthbg-700 rounded-md text-red-glow">
          <SubrouteLink text="events.about" to="" id={event.id} />
          <SubrouteLink text="events.photos" to="/photos" id={event.id} />

          {event.export &&
            event.export.status.value === "complete" &&
            event.export?.timelapse && (
              <SubrouteLink
                text="events.timelapse"
                to="/timelapse"
                id={event.id}
              />
            )}
          <SubrouteLink
            text="events.participants"
            to="/participants"
            id={event.id}
          />
          <SubrouteLink text="events.karaoke.title" to="/songs" id={event.id} />
          {tokenUser?.id === event.owner?.id && (
            <SubrouteLink
              text="events.settings.title"
              to="/settings"
              id={event.id}
            />
          )}
        </div>
      </Card>

      <Card className="w-full overflow-y-auto">
        <Outlet />
      </Card>
    </div>
  );
}
