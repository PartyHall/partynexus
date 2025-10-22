import EventForm from "@/components/event/form";
import Card from "@/components/generic/card";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/_authenticated/new")({
  component: RouteComponent,
});

function RouteComponent() {
  return (
    <Card className="pageContainer sm:w-150! flex flex-col gap-4! mt-5">
      <EventForm />
    </Card>
  );
}
