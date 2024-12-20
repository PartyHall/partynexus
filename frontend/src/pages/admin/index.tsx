import { Button, Flex, Typography } from 'antd';
import { IconUser, IconVersions } from '@tabler/icons-react';
import { useNavigate } from 'react-router-dom';
import { useTitle } from 'ahooks';
import { useTranslation } from 'react-i18next';

export default function AdminIndexPage() {
    const { t } = useTranslation();
    const navigate = useNavigate();

    useTitle(t('menu.admin.title') + ' - PartyHall');

    return (
        <Flex vertical gap={8} wrap="wrap">
            <Typography.Title
                className="blue-glow"
                style={{ marginBottom: '1em' }}
            >
                {t('menu.admin.title')}
            </Typography.Title>

            <Button
                onClick={() => navigate('/admin/users')}
                icon={<IconUser size={20} />}
            >
                {t('menu.admin.user_management')}
            </Button>
            <Button
                onClick={() => navigate('/admin/backdrops')}
                icon={<IconVersions size={20} />}
            >
                {t('menu.admin.backdrop_management')}
            </Button>
        </Flex>
    );
}
