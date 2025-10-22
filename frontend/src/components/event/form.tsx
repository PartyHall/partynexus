import type { Event, UpsertEvent } from "@/types/event";
import { useForm } from "react-hook-form";
import Title from "../generic/title";
import { useTranslation } from "react-i18next";
import Input, { DateTimeInput } from "../generic/input";
import Button from "../generic/button";
import { createEvent, updateEvent } from "@/api/events";
import { useState } from "react";
import { enqueueSnackbar } from "notistack";
import { ValidationError } from "@/api/violations_error";
import { useRouter } from "@tanstack/react-router";

type Props = {
  event?: Event | null;
};

export default function EventForm({ event }: Props) {
  const { t } = useTranslation();
  const { navigate } = useRouter();
  const [globalErrors, setGlobalErrors] = useState<string[]>([]);

  const {
    control,
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<UpsertEvent>({
    defaultValues: {
      name: event?.name || "",
      author: event?.author || "",
      location: event?.location || "",
      datetime: event?.datetime || "",
    },
  });

  const onSubmit = async (data: any) => {
    setGlobalErrors([]);

    try {
      let eventId = event?.id || null;
      if (event?.id) {
        await updateEvent(event.id, data);
      } else {
        const newEvent = await createEvent(data);
        eventId = newEvent.id;
      }

      if (!eventId) {
        throw new Error("Event ID is not defined after upsert");
      }

      navigate({ to: "/$id", params: { id: eventId } });
      enqueueSnackbar(t("generic.changes_saved"), { variant: "success" });
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
    <form
      className="flex flex-col gap-4 w-full"
      onSubmit={handleSubmit(onSubmit)}
    >
      <Title className="text-center" noMargin>
        {t("events.editor.title_" + (event ? "edit" : "new"), {
          name: event?.name || "",
        })}
      </Title>

      <Input
        label={t("events.editor.name")}
        {...register("name", { required: true })}
        required
        error={errors.name}
        disabled={isSubmitting}
      />

      <DateTimeInput
        name="datetime"
        label={t("generic.date")}
        control={control}
        error={errors.datetime}
        disabled={isSubmitting}
        required
      />

      <Input
        label={t("events.made_by")}
        {...register("author")}
        error={errors.author}
        disabled={isSubmitting}
      />

      <Input
        label={t("events.located_at")}
        {...register("location")}
        error={errors.location}
        disabled={isSubmitting}
      />

      {globalErrors.length > 0 && (
        <div className="text-red-glow mb-4">
          {globalErrors.map((error, index) => (
            <p key={index}>{error}</p>
          ))}
        </div>
      )}

      <Button>{t("generic.save")}</Button>
    </form>
  );
}
