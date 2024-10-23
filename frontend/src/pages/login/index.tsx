import { Flex, Tabs } from "antd";
import { useEffect, useState } from "react";

import LoginTab from "./LoginTab";
import PhLogo from '../../assets/ph_logo_sd.webp';
import RegisterTab from "./RegisterTab";
import { useAuth } from "../../hooks/auth";
import { useNavigate } from "react-router-dom";
import { useTitle } from "ahooks";
import { useTranslation } from "react-i18next";


export default function LoginPage() {
    const {t} = useTranslation();
    const {api, isLoggedIn} = useAuth();
    const navigate = useNavigate();

    const [page, setPage] = useState<string>('login.tab_login');

    useTitle(`${t(page)} - PartyHall`);

    useEffect(() => {
        if (isLoggedIn()) {
            navigate('/');
        }
    }, [api.token]);

    const items = [
        {
            key: 'login',
            label: t('login.tab_login'),
            children: <LoginTab />,
        },
        {
            key: 'register',
            label: t('login.tab_register'),
            children: <RegisterTab />,
        }
    ]

    return <Flex vertical align="center" justify="center" style={{ height: '100%' }} gap={8}>
        <img src={PhLogo} alt="Partyhall logo" style={{ display: 'block', maxHeight: '3em' }} />
        <Tabs centered defaultActiveKey="login" items={items} onChange={x => setPage('login.tab_' + x)}/>
    </Flex>;
}
