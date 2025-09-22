import {
  createFileRoute,
  Outlet,
  redirect,
  useLocation,
  useNavigate,
} from "@tanstack/react-router";
import { useAuthStore } from "../../stores/auth";
import { useEffect } from "react";
import { TopBar } from "@/components/topbar";
import setI18NLanguage from "@/utils/lang";
import MercureProvider from "@/hooks/useMercure";

export const Route = createFileRoute("/_authenticated")({
  beforeLoad: async ({ location }) => {
    const { token, tokenUser, doRefresh } = useAuthStore.getState();

    if (!token || !tokenUser) {
      // fuck that
      let loc: string | undefined = location.pathname;
      if (loc === "/" || loc === "/login" || loc === "/account") {
        loc = undefined;
      }

      throw redirect({
        to: "/login",
        search: { redirect: loc },
        reloadDocument: true,
      });
    }

    setI18NLanguage(tokenUser.language);

    /**
     * If the token expires in less than 30 seconds,
     * Refresh it
     */
    if (Date.now() >= tokenUser.exp * 1000 - 30000) {
      await doRefresh();
    }
  },
  component: RouteComponent,
});

function RouteComponent() {
  const { tokenUser, refreshToken, doRefresh } = useAuthStore();
  const navigate = useNavigate();
  const location = useLocation();

  /**
   * Set a timeout to refresh the token
   * 30 seconds before it expires.
   *
   * This ensures that we always have a valid token
   * or the user gets logged out
   *
   * Also update the user language based on its token infos
   */
  useEffect(() => {
    if (!tokenUser || !refreshToken) {
      // fuck that
      let loc: string | undefined = location.pathname;
      if (loc === "/" || loc === "/login" || loc === "/account") {
        loc = undefined;
      }
      navigate({ to: "/login", search: { redirect: loc } });
      return;
    }

    setI18NLanguage(tokenUser.language);

    const interval = setTimeout(
      doRefresh,
      Math.abs(tokenUser.exp * 1000 - Date.now() - 30000),
    );

    return () => {
      clearTimeout(interval);
    };
  }, [tokenUser, refreshToken]);

  return (
    <>
      <TopBar />
      <div
        id="main_page_container"
        className="flex flex-col items-start w-full flex-1 overflow-auto"
      >
        <MercureProvider>
          <Outlet />
        </MercureProvider>
      </div>
    </>
  );
}
