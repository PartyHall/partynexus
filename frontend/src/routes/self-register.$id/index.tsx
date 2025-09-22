import { joinEvent } from "@/api/events/join";
import Button, { ButtonLink } from "@/components/generic/button";
import { useAuthStore } from "@/stores/auth";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useTranslation } from "react-i18next";
import { Route as RouteSelfRegister } from "./route";
import Translate from "@/components/generic/custom_translation";

export const Route = createFileRoute("/self-register/$id/")({
  component: RouteComponent,
});

function RouteComponent() {
  const { t } = useTranslation();
  const { id } = Route.useParams();
  const { token, tokenUser } = useAuthStore.getState();
  const navigate = useNavigate();

  const parentData = RouteSelfRegister.useLoaderData();

  const join = async () => {
    try {
      const event = await joinEvent(id);
      navigate({ to: "/$id", params: { id: event.id } });
    } catch (err) {
      console.error(err);
    }
  };

  return (
    <>
      <p>
        <Translate
          mapping="register.self.desc_1"
          values={{ eventName: parentData?.name || "" }}
        />
      </p>

      <p className="mt-2">{t("register.self.desc_2")}</p>

      <div className="flex flex-col w-1/2 gap-2 m-auto mt-5">
        {(!token || !tokenUser) && (
          <>
            <ButtonLink to="/self-register/$id/register" params={{ id }}>
              {t("register.register")}
            </ButtonLink>
            <ButtonLink
              to="/login"
              search={{ redirect: `/self-register/${id}` }}
            >
              {t("login.title")}
            </ButtonLink>
          </>
        )}

        {token && tokenUser && (
          <Button onClick={join}>
            <Translate
              mapping="register.self.join_as"
              values={{ username: tokenUser.username }}
            />
          </Button>
        )}
      </div>
    </>
  );
}
