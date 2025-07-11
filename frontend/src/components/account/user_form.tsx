import { customFetch } from "@/api/customFetch";
import { ValidationError } from "@/api/violations_error";
import type { User } from "@/types/user";
import { enqueueSnackbar } from "notistack";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import Input from "../generic/input";
import EnumSelect from "../generic/enum_select";
import Button from "../generic/button";
import { createUser, updateUser, type UpsertUser } from "@/api/users";

type Props = {
    user: User | null;
    onSuccess?: (user: User) => void;
};

export default function UserEditForm({ user, onSuccess }: Props) {
    const { t } = useTranslation();

    const [globalErrors, setGlobalErrors] = useState<string[]>([]);
    const {
        register,
        handleSubmit,
        setError,
        formState: { errors, isSubmitting, isDirty },
        reset,
    } = useForm<UpsertUser>({
        defaultValues: {
            username: user?.username || '',
            email: user?.email,
            firstname: user?.firstname,
            lastname: user?.lastname,
            language: user?.language,
        },
    });

    const submit = async (formData: UpsertUser) => {
        setGlobalErrors([]);

        try {
            let data = null;
            if (user?.id) {
                data = await updateUser(user.id, formData);
            } else {
                data = await createUser(formData);
            }
            
            reset(data);
            enqueueSnackbar(
                t('generic.changes_saved'),
                { variant: 'success' },
            );

            onSuccess?.(data);
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

    return <form onSubmit={handleSubmit(submit)} className='w-full flex flex-col gap-4'>
        <Input
            label={t('generic.username')}
            id='username'
            {...register('username')}
            error={errors.username}
            disabled={isSubmitting || !!user?.id} // @TODO: Admin can probably update the username
        />

        <Input
            label={t('generic.email')}
            id='email'
            type='email'
            {...register('email')}
            error={errors.email}
            disabled={isSubmitting}
        />

        <Input
            label={t('generic.firstname')}
            id='firstname'
            {...register('firstname')}
            error={errors.firstname}
            disabled={isSubmitting}
        />

        <Input
            label={t('generic.lastname')}
            id='lastname'
            {...register('lastname')}
            error={errors.lastname}
            disabled={isSubmitting}
        />

        <EnumSelect
            enumName='languages'
            label={t('account.language')}
            id='language-select'
            {...register('language')}
            error={errors.language}
            disabled={isSubmitting}
        />

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

        <Button type="submit" className="w-full" disabled={!isDirty || isSubmitting}>{t('generic.save')}</Button>
    </form>;
}