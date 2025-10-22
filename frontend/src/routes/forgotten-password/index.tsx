import { HttpError } from "@/api/http_error";
import { generateForgottenPassword } from "@/api/users/management";
import Button from "@/components/generic/button";
import Card from "@/components/generic/card";
import Input from "@/components/generic/input";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

export const Route = createFileRoute("/forgotten-password/")({
  component: RouteComponent,
});

function RouteComponent() {
  const { t } = useTranslation();
  const [globalErrors, setGlobalErrors] = useState<string>("");
  const navigate = useNavigate();

  const {
    register,
    handleSubmit,
    formState: { isSubmitting },
  } = useForm<{ email: string }>({
    defaultValues: { email: "" },
  });

  const onSubmit = async (data: { email: string }) => {
    try {
      await generateForgottenPassword(data.email);
      navigate({ to: "/forgotten-password/sent" });
    } catch (err) {
      if (err instanceof HttpError && err.status === 429) {
        setGlobalErrors(t("errors.429.message"));

        return;
      }
      console.error(err);
      setGlobalErrors(t("generic.error.generic"));
    }
  };

  return (
    <div className="flex h-full w-full items-center justify-center">
      <Card className="w-full sm:w-110 gap-4 flex flex-col text-justify">
        <h1 className="text-center text-2xl font-bold text-purple-glow">
          {t("forgotten_password.title")}
        </h1>

        <p className="text-center">{t("forgotten_password.desc_request")}</p>

        <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4">
          <Input
            type="email"
            {...register("email", { required: true })}
            label={t("generic.email")}
            required
          />

          {globalErrors.length > 0 && (
            <div className="text-red-glow text-center">{globalErrors}</div>
          )}

          <Button type="submit" disabled={isSubmitting}>
            {t("forgotten_password.request")}
          </Button>
        </form>
      </Card>
    </div>
  );
}
