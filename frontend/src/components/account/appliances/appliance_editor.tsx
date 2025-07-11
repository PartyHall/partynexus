import { customFetch } from "@/api/customFetch";
import { ValidationError } from "@/api/violations_error";
import Button from "@/components/generic/button";
import Card from "@/components/generic/card";
import Input, { CopyInput } from "@/components/generic/input";
import Title from "@/components/generic/title";
import type { Appliance } from "@/types/appliance";
import { useNavigate } from "@tanstack/react-router";
import { enqueueSnackbar } from "notistack";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

type Props = {
    appliance: Appliance | null;
    doInvalidateRoute?: () => void;
}

type ApplianceForm = {
    name: string;
}

export default function ApplianceEditor({ appliance, doInvalidateRoute }: Props) {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const isCreating = !appliance;

    const [globalErrors, setGlobalErrors] = useState<string[]>([]);

    const {
        register,
        handleSubmit,
        setError,
        formState: { errors, isSubmitting, isDirty },
        reset,
    } = useForm<ApplianceForm>({
        defaultValues: {
            name: appliance?.name || '',
        }
    });

    const save = async (data: ApplianceForm) => {
        setGlobalErrors([]);

        try {
            const resp = await customFetch(
                isCreating ? `/api/appliances` : appliance['@id'],
                {
                    method: isCreating ? 'POST' : 'PATCH',
                    body: JSON.stringify({ name: data.name })
                });

            const respData = await resp.json();

            reset(respData);
            enqueueSnackbar(
                t('generic.changes_saved'),
                { variant: 'success' },
            );

            if (isCreating) {
                navigate({
                    to: `/account/appliances/$id`,
                    params: { id: respData.id },
                })
            } else {
                doInvalidateRoute?.();
            }
        } catch (err: any) {
            if (err instanceof ValidationError) {
                const globalErrors = err.applyToReactHookForm(setError);
                if (globalErrors.length > 0) {
                    setGlobalErrors(globalErrors);
                }

                return;
            }

            console.error('Error upserting appliance:', err);
            setGlobalErrors([t('generic.error.generic')]);
        }
    };

    return (
        <div className="pageContainer">
            <Card>
                <Title>{t(`account.my_appliances.editor.title_${isCreating ? 'new' : 'edit'}`, { name: appliance?.name })}</Title>
                <form className="w-full flex flex-col gap-1" onSubmit={handleSubmit(save)}>
                    {!isCreating && <span>{t('account.my_appliances.editor.id')}: {appliance?.id}</span>}
                    <Input
                        label={t('account.my_appliances.editor.name')}
                        {...register('name', { required: true })}
                        error={errors.name}
                        disabled={isSubmitting}
                    />

                    {
                        !isCreating && <>
                            <CopyInput
                                label={t('account.my_appliances.editor.hwid')}
                                value={appliance?.hardwareId}
                                copiedMessage={t('account.my_appliances.editor.hwid_copied')}
                            />
                            <CopyInput
                                label={t('account.my_appliances.editor.token')}
                                value={appliance?.apiToken}
                                copiedMessage={t('account.my_appliances.editor.token_copied')}
                            />
                        </>
                    }

                    {
                        globalErrors.length > 0 && (
                            <div className="text-red-glow mb-4">
                                {
                                    globalErrors.map((error, index) => (
                                        <p key={index}>{error}</p>
                                    ))
                                }
                            </div>
                        )
                    }

                    <div className="flex justify-end mt-4">
                        <Button disabled={!isDirty || isSubmitting}>
                            {isCreating && t('generic.create')}
                            {!isCreating && t('generic.save')}
                        </Button>
                    </div>
                </form>
            </Card>
        </div>
    );
}