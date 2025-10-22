import Card from "@/components/generic/card";
import { createFileRoute } from "@tanstack/react-router";
import { useTranslation } from "react-i18next";

export const Route = createFileRoute("/forgotten-password/sent")({
  component: RouteComponent,
});

function RouteComponent() {
  const { t } = useTranslation();

  return (
    <div className="flex h-full w-full items-center justify-center">
      <Card className="w-full m-4 sm:w-110 gap-4 flex flex-col text-justify">
        <h1 className="text-center text-2xl font-bold text-purple-glow">
          {t("forgotten_password.title")}
        </h1>
        <p>{t("forgotten_password.sent_1")}</p>
        <p>{t("forgotten_password.sent_2")}</p>
      </Card>
    </div>
  );
}
