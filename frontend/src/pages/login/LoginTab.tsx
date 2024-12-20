import { Button, Flex, Form, Input, Typography } from 'antd';
import { IconLock, IconLogin, IconUser } from '@tabler/icons-react';
import { FormItem } from 'react-hook-form-antd';
import { SdkError } from '../../sdk/responses/error';
import { useAuth } from '../../hooks/auth';
import { useForm } from 'react-hook-form';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type LoginForm = {
    username: string;
    password: string;
};

export default function LoginTab() {
    const { t } = useTranslation();
    const { login } = useAuth();
    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);

    const { control, handleSubmit, resetField } = useForm<LoginForm>({
        defaultValues: { username: '', password: '' },
    });

    const doLogin = async (data: LoginForm) => {
        if (loading) {
            return;
        }

        setLoading(true);
        try {
            await login(data.username, data.password);
        } catch (e) {
            setError(
                e instanceof SdkError ? e.message : t('generic.error.unknown')
            );
            resetField('password');
        }
        setLoading(false);
    };

    return (
        <Form
            style={{ maxWidth: 600, marginTop: 16 }}
            onFinish={handleSubmit(doLogin)}
        >
            <FormItem control={control} name="username">
                <Input
                    prefix={<IconUser size={20} />}
                    placeholder={t('generic.username')}
                    required
                />
            </FormItem>

            <FormItem control={control} name="password">
                <Input.Password
                    prefix={<IconLock size={20} />}
                    placeholder={t('generic.password')}
                    required
                />
            </FormItem>

            {error && (
                <Flex align="center" justify="center">
                    <Typography.Text type="danger">{error}</Typography.Text>
                </Flex>
            )}

            <Flex align="center" justify="center" style={{ marginTop: 32 }}>
                <Form.Item>
                    <Button
                        type="primary"
                        htmlType="submit"
                        disabled={loading}
                        icon={<IconLogin size={20} />}
                    >
                        {t('login.login_bt')}
                    </Button>
                </Form.Item>
            </Flex>
        </Form>
    );
}
