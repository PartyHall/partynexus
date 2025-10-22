import { createFileRoute } from "@tanstack/react-router";
import { Route as ParentRoute } from "./route.tsx";
import EventForm from "@/components/event/form.tsx";

export const Route = createFileRoute("/_authenticated/$id/edit")({
  component: RouteComponent,
});

function RouteComponent() {
  const event = ParentRoute.useLoaderData();

  return <EventForm event={event} />;
}
