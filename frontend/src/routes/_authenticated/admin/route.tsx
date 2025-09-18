import { createFileRoute, Outlet } from "@tanstack/react-router";
import { useAuthStore } from "@/stores/auth";
import { HttpError } from "@/api/http_error";

export const Route = createFileRoute("/_authenticated/admin")({
  beforeLoad: async () => {
    const { isGranted } = useAuthStore.getState();

    if (!isGranted("ROLE_ADMIN")) {
      // @TODO: translate
      throw new HttpError({
        message: "You are not an admin",
        status: 403,
      });
    }
  },
  component: RouteComponent,
});

function RouteComponent() {
  return <Outlet />;
}
