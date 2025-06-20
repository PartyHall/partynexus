import { createFileRoute, Link, useNavigate } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next';
import Card from '@/components/generic/card';
import { useForm } from 'react-hook-form';
import { HttpError } from '@/api/http_error';
import { useEffect, useState } from 'react';
import { login } from '@/api/auth';
import { useAuthStore } from '@/stores/auth';
import Input from '@/components/generic/input';
import { IconAsterisk, IconUser } from '@tabler/icons-react';
import Button from '@/components/generic/button';

export const Route = createFileRoute('/login/')({
  component: RouteComponent,
})

type LoginForm = {
  username: string;
  password: string;
};

function RouteComponent() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const setToken = useAuthStore(state => state.setToken);
  const { handleSubmit, register, watch, resetField } = useForm<LoginForm>();

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const [username, password] = watch(['username', 'password']);
  useEffect(() => {
    if (!password) {
      return;
    }

    setErrorMessage(null)
  }, [username, password]);

  const onSubmit = async (data: LoginForm) => {
    setErrorMessage(null);
    setIsSubmitting(true);

    try {
      const resp = await login(data.username, data.password);

      setToken(resp.token, resp.refresh_token);
      navigate({ to: '/' });
      setIsSubmitting(false);
    } catch (err: any) {
      setErrorMessage(
        err instanceof HttpError
          ? err.message
          : t('generic.error.generic')
      );
      resetField('password');
      setIsSubmitting(false);
    }
  };

  return <div className='flex-col-center h-full'>
    <Card>
      <img
        src='/assets/ph_logo_sd.webp'
        alt="PartyHall logo"
        className='w-60'
      />

      <form className='flex flex-col gap-2 w-full' onSubmit={handleSubmit(onSubmit)}>
        <Input
          label={t('generic.username')}
          id='username'
          icon={<IconUser />}
          autoFocus
          autoComplete="username"
          disabled={isSubmitting}
          {...register('username')}
        />

        <Input
          label={t('generic.password')}
          id='password'
          icon={<IconAsterisk />}
          type='password'
          autoComplete="password"
          disabled={isSubmitting}
          {...register('password')}
        />

        <div className='text-center p-2'>
          <Link to='/forgotten-password'>{t('login.forgot_password')}</Link>
        </div>

        {
          errorMessage
          && <div className='text-red-glow text-center'>
            {errorMessage}
          </div>
        }

        <Button type="submit" disabled={isSubmitting}>
          {t('login.login_button')}
        </Button>

        <div className='text-center mt-4'>
          <Link to='/register'>{t('login.register_prompt')}</Link>
        </div>
      </form>
    </Card>
  </div>
}

