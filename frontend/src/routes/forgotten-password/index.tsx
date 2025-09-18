import Card from "@/components/generic/card";
import { createFileRoute } from "@tanstack/react-router";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

/**
 * @TODO: Continue this
 */

export const Route = createFileRoute("/forgotten-password/")({
  component: RouteComponent,
});

function RouteComponent() {
  const { t } = useTranslation();

  const { handleSubmit } = useForm<{ email: string }>({
    defaultValues: { email: "" },
  });

  const onSubmit = async (data: { email: string }) => {
    console.log(data);
  };

  return (
    <div className="flex h-full w-full items-center justify-center">
      <Card className="w-full sm:w-110 gap-4 flex flex-col text-justify">
        <h1 className="text-center text-2xl font-bold">
          {t("forgotten_password.title")}
        </h1>

        <p className="text-center">{t("forgotten_password.desc_request")}</p>

        <form
          onSubmit={handleSubmit(onSubmit)}
          className="flex flex-col gap-4"
        ></form>
      </Card>
    </div>
  );
}
