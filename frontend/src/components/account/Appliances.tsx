import { Button, Flex, Typography } from "antd";

import Appliance from "./Appliance";
import { IconPlus } from "@tabler/icons-react";
import { useAuth } from "../../hooks/auth";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";

export default function Appliances() {
    const {user} = useAuth();
    const {t} = useTranslation();
    const navigate = useNavigate();

    return <Flex vertical gap={8}>
        <Flex align="center" justify="space-between">
            <Typography.Title className="blue-glow" level={2} style={{margin: 0}}>{t('my_account.my_appliances')}</Typography.Title>
            <Button icon={<IconPlus />} onClick={() => navigate('/appliances/new')}>{t('my_account.new')}</Button>
        </Flex>
        { user?.appliances.map(x => <Appliance key={x.id} appliance={x}/>) }
    </Flex>
}