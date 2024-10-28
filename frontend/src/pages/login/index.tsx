import { Flex, Tabs } from "antd";

import LoginTab from "./LoginTab";
import MagicLinkLoginTab from "./MagicLoginTab";
import PhLogo from '../../assets/ph_logo_sd.webp';

import { useAuth } from "../../hooks/auth";
import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useTitle } from "ahooks";
import { useTranslation } from "react-i18next";


export default function LoginPage() {
    const { t } = useTranslation();
    const { api, isLoggedIn } = useAuth();
    const navigate = useNavigate();

    useTitle(`${t('login.tab_login')} - PartyHall`);

    useEffect(() => {
        if (isLoggedIn()) {
            navigate('/');
        }
    }, [api.token]);

    const items = [
        {
            key: 'magic_login',
            label: t('login.tab_magic_login'),
            children: <MagicLinkLoginTab />,
        },
        {
            key: 'login',
            label: t('login.tab_login_password'),
            children: <LoginTab />,
        },
    ]

    return <Flex vertical align="center" justify="center" style={{ height: '100%' }} gap={8}>
        <img src={PhLogo} alt="Partyhall logo" style={{ display: 'block', maxHeight: '3em' }} />
        <Tabs centered defaultActiveKey="magic_login" items={items} />
    </Flex>;
}
