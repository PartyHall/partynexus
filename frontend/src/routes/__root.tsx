import { Outlet, createRootRoute } from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import {
  FuzzyErrorComponent,
  NotFoundErrorComponent,
} from "../components/generic/error";

function Loader() {
  return <div style={{ textAlign: "center", marginTop: 40 }}>Chargement…</div>;
}

export const Route = createRootRoute({
  component: () => (
    <>
      <Outlet />
      <ReactQueryDevtools initialIsOpen={false} />
      <TanStackRouterDevtools />
    </>
  ),
  notFoundComponent: NotFoundErrorComponent,
  errorComponent: ({ error }) => <FuzzyErrorComponent error={error} />,
  pendingComponent: Loader,
});
