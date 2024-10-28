import { Flex, Typography } from "antd";
import { useEffect, useState } from "react";
import { useNavigate, useSearchParams } from "react-router-dom"

import PhLogo from '../../assets/ph_logo_sd.webp';

import { useAsyncEffect } from "ahooks";
import { useAuth } from "../../hooks/auth";
import { useTranslation } from "react-i18next";

export default function MagicLoginPage() {
    const [searchParams,] = useSearchParams();
    const { t } = useTranslation();
    const navigate = useNavigate();

    const { magicLogin, isLoggedIn, api } = useAuth();
    const [error, setError] = useState<string|null>(null);

    useAsyncEffect(async () => {
        const email = searchParams.get('email');
        const code = searchParams.get('code');

        if (!email || !code) {
            return;
        }

        try {
            await magicLogin(email, code);
        } catch (e) {
            setError(e as unknown as string);
        }
    }, [searchParams]);

    useEffect(() => {
        if (isLoggedIn()) {
            navigate('/');
            return;
        }
    }, [api]);

    return <Flex vertical align="center" justify="center" style={{ height: '100%' }}>
        <Flex vertical align="center" justify="center" gap={16}>
            <img src={PhLogo} alt="Partyhall logo" style={{ display: 'block', maxHeight: '4em' }} />
            {
                !error &&
                <>
                    <Typography.Title style={{ margin: 0 }}>{t('login.magic_login_callback.title')}</Typography.Title>
                    <Typography.Text>{t('login.magic_login_callback.desc')}</Typography.Text>
                </>
            }
            {
                error &&
                <>
                    <Typography.Title style={{ margin: 0 }}>{t('login.magic_login_callback.fail.title')}</Typography.Title>
                    <Typography.Text>{t(error)}</Typography.Text>
                </>
            }
        </Flex>
    </Flex>
}