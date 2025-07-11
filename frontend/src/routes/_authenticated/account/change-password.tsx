import { customFetch } from '@/api/customFetch';
import { ValidationError } from '@/api/violations_error';
import Button from '@/components/generic/button';
import Card from '@/components/generic/card';
import Input from '@/components/generic/input';
import Title from '@/components/generic/title'
import useTranslatedTitle from '@/hooks/useTranslatedTitle'
import { useAuthStore } from '@/stores/auth';
import { createFileRoute } from '@tanstack/react-router'
import { enqueueSnackbar } from 'notistack';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/account/change-password')({
  component: RouteComponent,
})

type PasswordChangeForm = {
  oldPassword: string;
  newPassword: string;
  confirmNewPassword: string;
}

function RouteComponent() {
  const { t } = useTranslation();
  const tokenUser = useAuthStore((state) => state.tokenUser);
  useTranslatedTitle('account.change_password.title');

  const [globalErrors, setGlobalErrors] = useState<string[]>([]);

  const {
    register,
    handleSubmit,
    setError,
    clearErrors,
    formState: { errors, isSubmitting },
    reset,
    watch,
  } = useForm<PasswordChangeForm>({});

  const newPassword = watch('newPassword');
  const confirmNewPassword = watch('confirmNewPassword');

  const onSubmit = async (data: PasswordChangeForm) => {
    if (!tokenUser) {
      return;
    }

    setGlobalErrors([]);

    try {
      await customFetch(`${tokenUser.iri}/set-password`, {
        method: 'POST',
        body: JSON.stringify({
          oldPassword: data.oldPassword,
          newPassword: data.newPassword,
        })
      });

      reset();
      enqueueSnackbar(
        t('generic.changes_saved'),
        { variant: 'success' },
      );
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

  useEffect(() => {
    if (
      newPassword
      && confirmNewPassword
      && newPassword !== confirmNewPassword
    ) {
      setError('confirmNewPassword', {
        type: 'manual',
        message: t('account.change_password.password_mismatch'),
      });
    } else {
      clearErrors('confirmNewPassword');
    }
  }, [newPassword, confirmNewPassword]);

  return <div className='pageContainer'>
    <Card className='w-full sm:w-100'>
      <Title center>{t('account.change_password.title')}</Title>

      <form className='w-full flex flex-col gap-4' onSubmit={handleSubmit(onSubmit)}>
        <Input
          type='password'
          label={t('account.change_password.current')}
          required
          {...register('oldPassword', { required: true })}
          error={errors.oldPassword}
          disabled={isSubmitting}
        />

        <Input
          type='password'
          label={t('account.change_password.new')}
          required
          {...register('newPassword', { required: true })}
          error={errors.newPassword}
          disabled={isSubmitting}
        />

        <Input
          type='password'
          label={t('account.change_password.confirm')}
          required
          {...register('confirmNewPassword', { required: true })}
          error={errors.confirmNewPassword}
          disabled={isSubmitting}
        />

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

        <div className='flex justify-center'>
          <Button
            type='submit'
            disabled={
              isSubmitting ||
              !newPassword || !confirmNewPassword ||
              newPassword !== confirmNewPassword
            }
          >
            {t('generic.save')}
          </Button>
        </div>
      </form>
    </Card>
  </div>
}
