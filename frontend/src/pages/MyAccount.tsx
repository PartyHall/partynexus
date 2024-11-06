import { Button, Flex, Typography } from "antd";
import { IconLogout, IconUser } from "@tabler/icons-react";
import { useAsyncEffect, useTitle } from "ahooks";

import AccountEditor from "../components/account/AccountEditor";
import Appliances from "../components/account/Appliances";
import Loader from "../components/Loader";

import { useAuth } from "../hooks/auth";
import { useNavigate } from "react-router-dom";
import { useState } from "react";
import { useTranslation } from "react-i18next";

export default function MyAccountPage() {
    const { api, isGranted, refreshUser, user } = useAuth();
    const { t } = useTranslation();
    const { logout } = useAuth();
    const navigate = useNavigate();

    const [loading, setLoading] = useState<boolean>(true);

    useTitle(t('my_account.title') + ' - PartyHall');

    useAsyncEffect(async () => {
        if (!api.tokenUser) {
            return;
        }

        setLoading(true);

        try {
            await refreshUser();
        } catch (e) {
            console.error(e);
        }

        setLoading(false);
    }, [api.tokenUser]);

    return <Flex vertical gap={8} style={{ height: '100%' }}>
        <Typography.Title className="blue-glow">{t('my_account.title')}</Typography.Title>
        <Loader loading={loading}>
            <Flex vertical flex={1}>
                <Flex vertical align="center">
                    {user && <AccountEditor user={user} />}
                </Flex>

                {user && isGranted('ROLE_ADMIN') && <Appliances />}
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