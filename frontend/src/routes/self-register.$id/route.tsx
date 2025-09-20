import { customFetch } from '@/api/customFetch';
import { HttpError } from '@/api/http_error';
import Card from '@/components/generic/card'
import { useAuthStore } from '@/stores/auth';
import setI18NLanguage from '@/utils/lang';
import { createFileRoute, Outlet } from '@tanstack/react-router'
import { useEffect } from 'react';
import { useTranslation } from 'react-i18next';

export const Route = createFileRoute('/self-register/$id')({
  component: RouteComponent,
  loader: async ({ params: { id } }) => {
    const { token, tokenUser, doRefresh } = useAuthStore.getState();

    if (token && tokenUser) {
      setI18NLanguage(tokenUser.language);

      /**
       * If the token expires in less than 30 seconds,
       * Refresh it
       */
      if (Date.now() >= tokenUser.exp * 1000 - 30000) {
        await doRefresh();
      }
    }

    let data = null;

    try {
      const resp = await customFetch(`/api/self_register/${id}`);
      data = await resp.json();
    } catch (err) {
      return err;
    }

    return data;
  }
})

function RouteComponent() {
  const { tokenUser, refreshToken, doRefresh } = useAuthStore.getState();
  const { t } = useTranslation();
  const resp = Route.useLoaderData();

  /**
   * Set a timeout to refresh the token
   * 30 seconds before it expires.
   *
   * This ensures that we always have a valid token
   * or the user gets logged out
   *
   * Also update the user language based on its token infos
   */
  useEffect(() => {
    if (!tokenUser || !refreshToken) {
      return;
    }

    setI18NLanguage(tokenUser.language);

    const interval = setTimeout(
      doRefresh,
      Math.abs(tokenUser.exp * 1000 - Date.now() - 30000),
    );

    return () => {
      clearTimeout(interval);
    };
  }, [tokenUser, refreshToken, doRefresh]);

  return <div className='w-full h-full flex flex-col items-center justify-center'>
    <Card className='flex flex-col max-w-full md:max-w-md mx-4'>
      <img
        src="/assets/ph_logo_sd.webp"
        alt="PartyHall logo"
        className="w-45 cursor-pointer m-auto mb-2"
      />

      {
        resp instanceof HttpError
        && <div className='text-center text-red-600 my-4'>
          {
            t(
              (resp.status !== 404 && resp.status !== 400)
                ? resp.message
                : `register.self.${resp.status}`,
            )
          }
        </div>
      }

      {
        !(resp instanceof HttpError)
        && <Outlet />
      }
    </Card>
  </div>
}
