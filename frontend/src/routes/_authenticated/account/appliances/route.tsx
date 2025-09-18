import { makeError } from "@/api/http_error";
import { useAuthStore } from "@/stores/auth";
import { createFileRoute, Outlet } from "@tanstack/react-router";

export const Route = createFileRoute("/_authenticated/account/appliances")({
  beforeLoad: async () => {
    const isGranted = useAuthStore.getState().isGranted;

    if (!isGranted("ROLE_EVENT_MAKER")) {
      throw makeError(403);
    }
  },
  component: RouteComponent,
});

function RouteComponent() {
  return <Outlet />;
}
