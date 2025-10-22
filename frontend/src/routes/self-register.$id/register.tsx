import registerUserEvent from "@/api/users/registration";
import { ValidationError } from "@/api/violations_error";
import Button from "@/components/generic/button";
import EnumSelect from "@/components/generic/enum_select";
import Input from "@/components/generic/input";
import { useAuthStore } from "@/stores/auth";
import type { RegistrationUser } from "@/types/user";
import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { enqueueSnackbar } from "notistack";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { Route as ParentRoute } from "./route";

export const Route = createFileRoute("/self-register/$id/register")({
  component: RouteComponent,
});

function RouteComponent() {
  const { id } = Route.useParams();
  const event = ParentRoute.useLoaderData();
  const { t } = useTranslation();
  const { setToken } = useAuthStore();
  const navigate = useNavigate();

  const [globalErrors, setGlobalErrors] = useState<string[]>([]);
  const [passwordRepeat, setPasswordRepeat] = useState<string>("");

  const {
    // control,
    register,
    handleSubmit,
    setError,
    reset,
    formState: { errors, isSubmitting },
    watch,
  } = useForm<RegistrationUser>({
    defaultValues: {
      username: "",
      email: "",
      firstname: "",
      lastname: "",
      newPassword: "",
      language: "/api/languages/en_US", // Maybe the backend has a default language at some point? In this case use it
    },
  });

  const password = watch("newPassword");

  const onSubmit = async (data: any) => {
    setGlobalErrors([]);

    try {
      const authentication = await registerUserEvent(id, data);

      setToken(authentication.token, authentication.refresh_token);

      navigate({ to: "/$id", params: { id: event.id } });
      enqueueSnackbar(t("generic.changes_saved"), { variant: "success" });
    } catch (err) {
      reset({ newPassword: "" });
      setPasswordRepeat("");

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
    <form
      className="flex flex-col gap-1 w-full"
      onSubmit={handleSubmit(onSubmit)}
    >
      <Input
        label={t("generic.username")}
        {...register("username", { required: true })}
        required
        error={errors.username?.message}
        disabled={isSubmitting}
      />

      <Input
        label={t("generic.email")}
        type="email"
        {...register("email", { required: true })}
        required
        error={errors.email?.message}
        disabled={isSubmitting}
      />

      <Input
        label={t("generic.firstname")}
        {...register("firstname", { required: true })}
        required
        error={errors.firstname?.message}
        disabled={isSubmitting}
      />

      <Input
        label={t("generic.lastname")}
        {...register("lastname")}
        error={errors.lastname?.message}
        disabled={isSubmitting}
      />

      <Input
        label={t("generic.password")}
        type="password"
        {...register("newPassword", { required: true })}
        error={errors.newPassword?.message}
        disabled={isSubmitting}
        required
      />

      <Input
        label={t("register.confirm_password")}
        type="password"
        disabled={isSubmitting}
        value={passwordRepeat}
        onChange={(e) => setPasswordRepeat(e.target.value)}
        required
      />

      {password.length > 0 &&
        passwordRepeat.length > 0 &&
        password !== passwordRepeat && (
          <span className="text-red-glow text-sm mt-1 text-center">
            {t("register.password_mismatch")}
          </span>
        )}

      <EnumSelect
        label={t("account.language")}
        enumName="languages"
        {...register("language", { required: true })}
        required
        error={errors.language?.message}
        disabled={isSubmitting}
      />

      {globalErrors.length > 0 && (
        <div className="text-red-glow mb-4">
          {globalErrors.map((error, index) => (
            <p key={index}>{error}</p>
          ))}
        </div>
      )}

      <Button
        type="submit"
        className="mt-4"
        disabled={isSubmitting || password !== passwordRepeat}
      >
        {t("register.register")}
      </Button>
    </form>
  );
}
