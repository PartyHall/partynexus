import { customFetch } from "@/api/customFetch";
import { HttpError } from "@/api/http_error";
import Card from "@/components/generic/card";
import Title from "@/components/generic/title";
import { useAuthStore } from "@/stores/auth";
import { createFileRoute } from "@tanstack/react-router";
import { useTranslation } from "react-i18next";
import { redirect } from "@tanstack/react-router";

export const Route = createFileRoute("/oauth-callback")({
  component: RouteComponent,
  validateSearch: (search) => {
    if (typeof search.code !== "string") {
      throw new Error("code is required");
    }
    return { code: search.code };
  },
  loaderDeps: ({ search: { code } }) => ({ code }),
  loader: async ({ deps: { code } }) => {
    const resp = await customFetch("/api/login_oauth", {
      method: "POST",
      body: JSON.stringify({ code }),
    });

    const data = await resp.json();

    if (!data.token || !data.refresh_token) {
      throw new HttpError({
        message: `errors.500.title`,
        submessage: `errors.500.message`,
        status: 500,
      });
    }

    const { setToken } = useAuthStore.getState();
    setToken(data.token, data.refresh_token);

    throw redirect({ to: "/" });
  },
});

function RouteComponent() {
  const { t } = useTranslation();

  return (
    <div className="w-full h-full flex flex-col justify-center items-center p-4">
      <Card className="text-center flex flex-col gap-5">
        <Title noMargin>{t("login.oauth.title")}</Title>
        <p>{t("login.oauth.desc")}</p>
      </Card>
    </div>
  );
}
