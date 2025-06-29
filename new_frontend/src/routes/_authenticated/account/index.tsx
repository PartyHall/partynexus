import { customFetch } from '@/api/customFetch';
import { getUser } from '@/api/users';
import { ValidationError } from '@/api/violations_error';
import Button from '@/components/generic/button';
import Card from '@/components/generic/card';
import EnumSelect from '@/components/generic/enum_select';
import Input from '@/components/generic/input';
import Title from '@/components/generic/title';
import useTranslatedTitle from '@/hooks/useTranslatedTitle';
import { useAuthStore } from '@/stores/auth'
import { createFileRoute, Link } from '@tanstack/react-router'
import { enqueueSnackbar } from 'notistack';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/_authenticated/account/')({
  component: RouteComponent,
  loader: async () => {
    const tokenUser = useAuthStore.getState().tokenUser;
    if (!tokenUser) {
      throw new Error('User not authenticated');
    }

    return await getUser(tokenUser.id);
  },
})

type UserAccountForm = {
  username: string;
  email: string;
  firstname: string;
  lastname: string;
  language: string;
};

function RouteComponent() {
  const data = Route.useLoaderData();
  const { t } = useTranslation();
  const { tokenUser, setToken, isGranted, doRefresh } = useAuthStore();

  useTranslatedTitle('account.title');

  const [isLoggingOut, setIsLoggingOut] = useState(false);

  const [globalErrors, setGlobalErrors] = useState<string[]>([]);
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting, isDirty },
    reset,
  } = useForm<UserAccountForm>({
    defaultValues: {
      username: data.username,
      email: data.email,
      firstname: data.firstname,
      lastname: data.lastname,
      language: data.language,
    },
  });

  const submit = async (formData: UserAccountForm) => {
    if (!tokenUser) {
      return;
    }

    setGlobalErrors([]);

    try {
      const resp = await customFetch(tokenUser.iri, {
        method: 'PATCH',
        body: JSON.stringify({
          username: formData.username,
          email: formData.email,
          firstname: formData.firstname,
          lastname: formData.lastname,
          language: formData.language,
        })
      });

      reset(await resp.json());
      enqueueSnackbar(
        t('generic.changes_saved'),
        { variant: 'success' },
      );

      if (tokenUser.language !== formData.language) {
        await doRefresh();
      }
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

  return <div className='pageContainer'>
    <Card className='w-full sm:w-150'>
      <Title center>{t('account.title')}</Title>
      <form onSubmit={handleSubmit(submit)} className='w-full flex flex-col gap-4'>
        <Input
          label={t('generic.username')}
          id='username'
          {...register('username')}
          error={errors.username}
          disabled={isSubmitting}
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
      </form>
    </Card>

    <Card className='w-full sm:w-150 flex flex-col gap-2 items-center'>
      <Title center>{t('account.actions')}</Title>
      {
        isGranted('ROLE_EVENT_MAKER')
        && <Link to="/account/appliances">{t('account.my_appliances.title')}</Link>
      }
      <Link to="/account/change-password">{t('account.change_password.title')}</Link>
      <Button className="w-full mt-4" onClick={() => {
        setIsLoggingOut(true);
        setToken(null, null);
      }} disabled={isLoggingOut}>{t('generic.logout')}</Button>
    </Card>
  </div>
}
