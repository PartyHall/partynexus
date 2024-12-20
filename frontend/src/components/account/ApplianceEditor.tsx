import { Button, Flex, Form, Input, Typography } from 'antd';
import CopyField from '../CopyField';
import { FormItem } from 'react-hook-form-antd';
import { IconDeviceFloppy } from '@tabler/icons-react';
import PnAppliance from '../../sdk/responses/appliance';
import { ValidationErrors } from '../../sdk/responses/validation_error';
import { useAuth } from '../../hooks/auth';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Props = {
    appliance: PnAppliance | null;
};

export default function ApplianceEditor({
    appliance: initialAppliance,
}: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [appliance, setAppliance] = useState<PnAppliance | null>(
        initialAppliance
    );
    const [notif, notifCtx] = useNotification();
    const navigate = useNavigate();

    const { control, handleSubmit, setError, formState } = useForm<PnAppliance>(
        {
            defaultValues: {
                id: appliance?.id,
                name: appliance?.name ?? '',
            },
        }
    );

    const doUpdateAppliance = async (data: PnAppliance) => {
        try {
            const resp = await api.appliances.upsert(data);
            if (resp) {
                if (resp.id && !initialAppliance?.id) {
                    navigate(`/appliances/${resp.id}`);

                    return;
                }

                setAppliance(resp);
            }
        } catch (e) {
            if (e instanceof ValidationErrors) {
                e.errors.forEach((x) =>
                    setError(x.fieldName as keyof PnAppliance, {
                        type: 'custom',
                        message: x.getText(),
                    })
                );
                return;
            }

            console.error(e);
            notif.error({
                message: 'Unknown error occured',
                description: 'See console for more details',
            });
        }
    };

    return (
        <Flex vertical align="center">
            <Typography.Title className="blue-glow">
                {t('my_account.appliance_editor.title')}
            </Typography.Title>

            <Form
                style={{ width: 500 }}
                layout="vertical"
                onFinish={handleSubmit(doUpdateAppliance)}
            >
                {appliance?.id && (
                    <Typography.Text style={{ fontSize: '1.1em' }}>
                        ID: {appliance.id}
                    </Typography.Text>
                )}

                <FormItem
                    control={control}
                    name="name"
                    label={t('my_account.appliance_editor.name') + ':'}
                >
                    <Input disabled={formState.isSubmitting} />
                </FormItem>

                {appliance?.id && (
                    <Flex vertical gap={4}>
                        <Typography.Text style={{ fontSize: '1.1em' }}>
                            Hardware ID:
                        </Typography.Text>
                        <CopyField text={appliance.hardwareId} />
                        <Typography.Text style={{ fontSize: '1.1em' }}>
                            Token:
                        </Typography.Text>
                        <CopyField text={appliance.apiToken} />
                    </Flex>
                )}

                <Flex align="center" justify="center" style={{ marginTop: 32 }}>
                    <Form.Item>
                        <Button
                            type="primary"
                            htmlType="submit"
                            disabled={formState.isSubmitting}
                            icon={<IconDeviceFloppy size={20} />}
                        >
                            {t('generic.save')}
                        </Button>
                    </Form.Item>
                </Flex>

                {notifCtx}
            </Form>
        </Flex>
    );
}
