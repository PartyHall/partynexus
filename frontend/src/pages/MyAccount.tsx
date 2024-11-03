import { Button, Flex, Typography } from "antd";
import { useAsyncEffect, useTitle } from "ahooks";

import AccountEditor from "../components/AccountEditor";
import { IconLogout, IconUser } from "@tabler/icons-react";
import Loader from "../components/Loader";
import { User } from "../sdk/responses/user";

import { useAuth } from "../hooks/auth";
import { useNavigate } from "react-router-dom";
import { useState } from "react";
import { useTranslation } from "react-i18next";

export default function MyAccountPage() {
    const { api, isGranted } = useAuth();
    const { t } = useTranslation();
    const { logout } = useAuth();
    const navigate = useNavigate();

    const [loading, setLoading] = useState<boolean>(true);
    const [user, setUser] = useState<User | null>(null);

    useTitle(t('my_account.title') + ' - PartyHall');

    useAsyncEffect(async () => {
        if (!api.tokenUser) {
            return;
        }

        setLoading(true);

        try {
            setUser(await api.users.getFromIri(api.tokenUser.iri));
        } catch (e) {
            console.error(e);
        }

        setLoading(false);
    }, [api.tokenUser]);

    return <Flex vertical gap={8} style={{ height: '100%' }}>
        <Typography.Title className="blue-glow">{t('my_account.title')}</Typography.Title>
        <Loader loading={loading}>
            <Flex vertical flex={1} align="center">
                {user && <AccountEditor user={user} />}
            </Flex>

            {
                isGranted('ROLE_ADMIN') && <Button
                    onClick={() => navigate('/admin/users')}
                    icon={<IconUser size={20} />}
                >
                    {t('my_account.user_management')}
                </Button>
            }

            <Button
                onClick={logout}
                icon={<IconLogout size={20} />}
            >
                {t('my_account.logout')}
            </Button>
        </Loader>
    </Flex>
}