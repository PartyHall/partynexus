import { getUser } from "@/api/users";
import UserEditForm from "@/components/account/user_form";
import Button from "@/components/generic/button";
import Card from "@/components/generic/card";
import Title from "@/components/generic/title";
import useTranslatedTitle from "@/hooks/useTranslatedTitle";
import { useAuthStore } from "@/stores/auth";
import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { useTranslation } from "react-i18next";

export const Route = createFileRoute("/_authenticated/account/")({
  component: RouteComponent,
  loader: async () => {
    const tokenUser = useAuthStore.getState().tokenUser;
    if (!tokenUser) {
      throw new Error("User not authenticated");
    }

    return await getUser(tokenUser.id);
  },
});

function RouteComponent() {
  const data = Route.useLoaderData();
  const { t } = useTranslation();
  const { tokenUser, setToken, isGranted, doRefresh } = useAuthStore();

  useTranslatedTitle("account.title");

  const [isLoggingOut, setIsLoggingOut] = useState(false);

  return (
    <div className="pageContainer">
      <Card className="w-full sm:w-150">
        <Title center>{t("account.title")}</Title>
        <UserEditForm
          user={data}
          onSuccess={async (user) => {
            if (tokenUser?.language !== user.language) {
              await doRefresh();
            }
          }}
        />
      </Card>

      <Card className="w-full sm:w-150 flex flex-col gap-2 items-center">
        <Title center>{t("account.actions")}</Title>
        {isGranted("ROLE_EVENT_MAKER") && (
          <Link to="/account/appliances">
            {t("account.my_appliances.title")}
          </Link>
        )}
        <Link to="/account/change-password">
          {t("account.change_password.title")}
        </Link>
        <Button
          className="w-full mt-4"
          onClick={() => {
            setIsLoggingOut(true);
            setToken(null, null);
          }}
          disabled={isLoggingOut}
        >
          {t("generic.logout")}
        </Button>
      </Card>
    </div>
  );
}
