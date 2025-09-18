import { HttpError } from "@/api/http_error";
import {
  checkForgottenPasswordValidity,
  setNewPassword,
} from "@/api/users/forgotten_password";
import { ValidationError } from "@/api/violations_error";
import Button, { ButtonLink } from "@/components/generic/button";
import Card from "@/components/generic/card";
import { FuzzyErrorComponent } from "@/components/generic/error";
import { PasswordInput } from "@/components/generic/input";
import useTranslatedTitle from "@/hooks/useTranslatedTitle";
import getUserName from "@/utils/get_user_name";
import { createFileRoute } from "@tanstack/react-router";
import { enqueueSnackbar } from "notistack";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

const ErrorComponent = ({ error }: any) => {
  const { t } = useTranslation();

  if (error instanceof HttpError && error.status === 404) {
    return (
      <div className="flex h-full w-full items-center justify-center">
        <Card className="w-full sm:w-110 gap-4 flex flex-col text-justify">
          <h1 className="text-center text-2xl font-bold">
            {t("forgotten_password.title")}
          </h1>

          <p className="text-red-glow text-center">
            {t("forgotten_password.not_found")}
          </p>
        </Card>
      </div>
    );
  }

  return <FuzzyErrorComponent error={error} />;
};

export const Route = createFileRoute("/forgotten-password/$id")({
  component: RouteComponent,
  loader: async ({ params }) => {
    const { id } = params;
    if (!id) {
      throw new Error("Forgotten password code is required");
    }

    return await checkForgottenPasswordValidity(id);
  },
  errorComponent: ErrorComponent,
});

type PasswordForgottenForm = {
  newPassword: string;
};

function RouteComponent() {
  const { t } = useTranslation();
  const { id } = Route.useParams();
  const data = Route.useLoaderData();

  useTranslatedTitle("forgotten_password.title");

  const [globalErrors, setGlobalErrors] = useState<string[]>([]);
  const [passwordUpdated, setPasswordUpdated] = useState(false);

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<PasswordForgottenForm>({
    defaultValues: { newPassword: "" },
  });

  const onSubmit = async (data: PasswordForgottenForm) => {
    setGlobalErrors([]);

    try {
      await setNewPassword(id, data.newPassword);
      setPasswordUpdated(true);
      enqueueSnackbar(t("forgotten_password.success"), { variant: "success" });
    } catch (err) {
      if (err instanceof ValidationError) {
        const globalErrors = err.applyToReactHookForm(setError);
        if (globalErrors.length > 0) {
          setGlobalErrors(globalErrors);
        }

        return;
      }

      console.error("Error upserting event:", err);
      setGlobalErrors([t("generic.error.generic")]);
    }
  };

  return (
    <div className="flex h-full w-full items-center justify-center">
      <Card className="w-full sm:w-110 gap-4 flex flex-col text-justify">
        <h1 className="text-center text-2xl font-bold">
          {t("forgotten_password.title")}
        </h1>
        {!passwordUpdated && (
          <>
            <p>
              {t("forgotten_password.desc", {
                username: getUserName(data.user),
              })}
            </p>

            <form
              className="flex flex-col gap-2"
              onSubmit={handleSubmit(onSubmit)}
            >
              <PasswordInput
                label={t("forgotten_password.password")}
                error={errors.newPassword}
                disabled={isSubmitting}
                required
                {...register("newPassword", {
                  required: t("generic.required"),
                })}
              />

              <PasswordInput
                label={t("forgotten_password.confirm_password")}
                disabled={isSubmitting}
                required
                {...register("newPassword", {
                  required: t("generic.required"),
                })}
              />

              {globalErrors.length > 0 && (
                <div className="text-red-glow">
                  {globalErrors.map((error, index) => (
                    <p key={index}>{error}</p>
                  ))}
                </div>
              )}

              <Button className="mt-2">
                {t("forgotten_password.set_password")}
              </Button>
            </form>
          </>
        )}
        {passwordUpdated && (
          <div className="flex flex-col items-center gap-4">
            <p className="text-green-glow">{t("forgotten_password.success")}</p>
            <ButtonLink to="/login">
              {t("forgotten_password.goto_login")}
            </ButtonLink>
          </div>
        )}
      </Card>
    </div>
  );
}
