import { createFileRoute, Outlet, redirect, useNavigate } from '@tanstack/react-router'
import { useAuthStore } from '../../stores/auth';
import { useEffect } from 'react';
import { TopBar } from '@/components/topbar';

export const Route = createFileRoute('/_authenticated')({
  beforeLoad: async () => {
    const { token, tokenUser, doRefresh } = useAuthStore.getState();

    if (!token || !tokenUser) {
      throw redirect({ to: '/login', reloadDocument: true });
    }

    /**
     * If the token expires in less than 30 seconds,
     * Refresh it
     */
    if (Date.now() >= ((tokenUser.exp * 1000) - 30000)) {
      await doRefresh();
    }
  },
  component: RouteComponent,
})

function RouteComponent() {
  const { tokenUser, refreshToken, doRefresh } = useAuthStore();
  const navigate = useNavigate();

  /**
   * Set a timeout to refresh the token
   * 30 seconds before it expires.
   * 
   * This ensures that we always have a valid token
   * or the user gets logged out
   */
  useEffect(() => {
    if (!tokenUser || !refreshToken) {
      navigate({ to: '/login' });
      return;
    }

    const interval = setTimeout(
      doRefresh,
      Math.abs((tokenUser.exp * 1000) - Date.now() - 30000),
    );

    return () => {
      clearTimeout(interval);
    }
  }, [tokenUser, refreshToken]);

  return <>
    <TopBar />
    <div id="main_page_container" className='flex flex-col items-start w-full flex-1 overflow-auto'>
      <Outlet />
    </div>
  </>
}
