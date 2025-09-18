import {
  createBackdropAlbum,
  updateBackdropAlbum,
  type UpsertBackdropAlbum,
} from "@/api/photobooth/backdrops";
import { ValidationError } from "@/api/violations_error";
import Button from "@/components/generic/button";
import Input from "@/components/generic/input";
import type { BackdropAlbum } from "@/types/backdrops";
import { useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

type Props = {
  album?: BackdropAlbum | null;
  onSuccess?: (album: BackdropAlbum) => void;
};

export default function BackdropAlbumForm({ album, onSuccess }: Props) {
  const { t } = useTranslation();
  const qc = useQueryClient();
  const [globalErrors, setGlobalErrors] = useState<string[]>([]);

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
    reset,
  } = useForm<UpsertBackdropAlbum>({
    defaultValues: {
      title: album?.title || "",
      author: album?.author || "",
      version: album?.version || 1,
    },
  });

  const onSubmit = async (data: UpsertBackdropAlbum) => {
    setGlobalErrors([]);

    /** Meh we'll need to do something cleaner */
    data.version = parseInt(data.version.toString(), 10);

    try {
      let updatedAlbum: BackdropAlbum | null = null;
      if (album?.id) {
        updatedAlbum = await updateBackdropAlbum(album.id, data);
      } else {
        updatedAlbum = await createBackdropAlbum(data);
      }

      reset(updatedAlbum);
      setGlobalErrors([]);

      qc.removeQueries({ queryKey: ["backdrop_albums"] });
      onSuccess?.(updatedAlbum);
    } catch (err: any) {
      if (err instanceof ValidationError) {
        const globalErrors = err.applyToReactHookForm(setError);
        if (globalErrors.length > 0) {
          setGlobalErrors(globalErrors);
        }

        return;
      }

      console.error("Error updating user account:", err);
      setGlobalErrors([t("generic.error.generic")]);
    }
  };

  return (
    <form
      className="flex flex-col w-full gap-2"
      onSubmit={handleSubmit(onSubmit)}
    >
      <Input
        label={t("admin.backdrop_albums.album_title")}
        {...register("title", { required: true })}
        required
        error={errors.title}
        disabled={isSubmitting}
      />

      <Input
        label={t("admin.backdrop_albums.author")}
        {...register("author", { required: true })}
        required
        error={errors.author}
        disabled={isSubmitting}
      />

      <Input
        label={t("admin.backdrop_albums.version")}
        {...register("version", { required: true })}
        type={album ? "number" : "hidden"}
        required
        error={errors.version}
        disabled={isSubmitting}
      />

      {globalErrors.length > 0 && (
        <div className="text-red-glow mb-4">
          {globalErrors.map((error, index) => (
            <p key={index}>{error}</p>
          ))}
        </div>
      )}

      {album && (
        <p className="text-primary-100">
          {t("admin.backdrop_albums.note_appliance_update")}
        </p>
      )}

      <Button className="mt-3">{t("generic.save")}</Button>
    </form>
  );
}
