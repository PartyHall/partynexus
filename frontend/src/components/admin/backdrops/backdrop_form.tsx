import { createBackdrop, updateBackdrop, type UpsertBackdrop } from "@/api/photobooth/backdrops";
import { ValidationError } from "@/api/violations_error";
import Button from "@/components/generic/button";
import Input from "@/components/generic/input";
import Modal from "@/components/generic/modal";
import UploadButton from "@/components/generic/upload_button";
import type { Backdrop } from "@/types/backdrops";
import { useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

type Props = {
    albumIri: string;
    backdrop?: Backdrop | null;
    onSuccess?: (backdrop: Backdrop | null) => void;
};

export default function BackdropForm({ albumIri, backdrop, onSuccess }: Props) {
    const { t } = useTranslation();
    const qc = useQueryClient();
    const [globalErrors, setGlobalErrors] = useState<string[]>([]);

    const {
        register,
        handleSubmit,
        setError,
        setValue,
        formState: { errors, isSubmitting },
        reset,
    } = useForm<UpsertBackdrop>({
        defaultValues: {
            title: backdrop?.title || '',
            album: albumIri,
            file: null,
        },
    });

    const onSubmit = async (data: UpsertBackdrop) => {
        setGlobalErrors([]);

        try {
            let updatedBackdrop: Backdrop | null = null;
            if (backdrop?.id) {
                updatedBackdrop = await updateBackdrop(albumIri, backdrop.id, data);
            } else {
                updatedBackdrop = await createBackdrop(data);
            }

            setGlobalErrors([]);

            qc.removeQueries({ queryKey: ['backdrop_albums'] });
            onSuccess?.(updatedBackdrop);

            reset();
        } catch (err: any) {
            if (err instanceof ValidationError) {
                const globalErrors = err.applyToReactHookForm(setError);
                if (globalErrors.length > 0) {
                    setGlobalErrors(globalErrors);
                }

                return;
            }

            console.error('Error updating user account:', err);
            setGlobalErrors([t('generic.error.generic')]);
        }
    };

    return <form className="flex flex-col w-full gap-2" onSubmit={handleSubmit(onSubmit)}>
        <Input
            label={t('generic.title')}
            {...register('title', { required: true })}
            required
            error={errors.title}
            disabled={isSubmitting}
        />

        {
            /** @TODO: Make a custom file input component that works proprely */
            !backdrop?.id && <label className="flex flex-col w-full gap-0.5̀">
                <span>{t('admin.backdrop_albums.backdrops.image')}:<span className="text-red-glow"> * </span></span>
                <input
                    type='file'
                    onChange={(e) => {
                        const file = e.target.files?.[0] || null;
                        setValue('file', file);
                    }}
                    required
                />
            </label>
        }

        {
            globalErrors.length > 0 && (
                <div className="text-red-glow">
                    {
                        globalErrors.map((error, index) => (
                            <p key={index}>{error}</p>
                        ))
                    }
                </div>
            )
        }

        <Button className="mt-3">{t('generic.save')}</Button>
    </form>;
}

type ModalProps = {
    open: boolean;
    onClose: () => void;
    onSuccess?: () => void;
    albumIri: string;
    backdrop?: Backdrop | null;
};

export function BackdropFormModal({ open, onClose, onSuccess, albumIri, backdrop }: ModalProps) {
    const { t } = useTranslation();

    return <Modal
        open={open}
        onOpenChange={onClose}
        title={t("admin.backdrop_albums.backdrops." + (backdrop ? 'edit_title' : 'new_title'), { title: backdrop?.title })}
        description={t("admin.backdrop_albums.backdrops." + (backdrop ? 'edit_title' : 'new_title'), { title: backdrop?.title })}
    >
        <BackdropForm albumIri={albumIri} backdrop={backdrop} onSuccess={() => {
            onSuccess?.();
            onClose();
        }} />
    </Modal>
}