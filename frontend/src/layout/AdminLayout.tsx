import { Outlet, useNavigate } from 'react-router-dom';

import { useAuth } from '../hooks/auth';
import { useEffect } from 'react';

export default function AdminLayout() {
    const { isGranted, api } = useAuth();
    const navigate = useNavigate();

    useEffect(() => {
        if (!api || !isGranted('ROLE_ADMIN')) {
            navigate('/');
        }
    }, [api]);

    return <Outlet />;
}
