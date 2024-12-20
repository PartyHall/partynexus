import { Button, Card, Flex, Popconfirm, Tooltip, Typography } from 'antd';
import { IconEdit, IconTrash } from '@tabler/icons-react';
import PnAppliance from '../../sdk/responses/appliance';
import { useAuth } from '../../hooks/auth';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

export default function Appliance({ appliance }: { appliance: PnAppliance }) {
    const { t } = useTranslation();
    const { api, refreshUser } = useAuth();
    const navigate = useNavigate();

    const onDelete = async () => {
        await api.appliances.delete(appliance.id);
        await refreshUser();
    };

    return (
        <Card>
            <Flex justify="space-between">
                <Typography.Text>{appliance.name}</Typography.Text>

                <Flex gap={4}>
                    <Tooltip title={t('my_account.edit')}>
                        <Button
                            icon={<IconEdit size={20} />}
                            onClick={() =>
                                navigate(`/appliances/${appliance.id}`)
                            }
                        />
                    </Tooltip>

                    <Popconfirm
                        title={t('my_account.delete_appliance.title')}
                        cancelText={t('generic.cancel')}
                        okText={t('generic.modal_im_sure')}
                        onConfirm={onDelete}
                    >
                        <Tooltip title={t('my_account.delete_appliance.name')}>
                            <Button icon={<IconTrash size={20} />} />
                        </Tooltip>
                    </Popconfirm>
                </Flex>
            </Flex>
        </Card>
    );
}
