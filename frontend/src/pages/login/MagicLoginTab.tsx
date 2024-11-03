import { Button, Flex, Form, Input, Typography } from "antd";
import { IconLogin, IconMail } from "@tabler/icons-react";
import { FormItem } from "react-hook-form-antd";

import { useAuth } from "../../hooks/auth";
import { useForm } from "react-hook-form";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type RegisterForm = {
    email: string;
};

export default function MagicLinkLoginTab() {
    const { t } = useTranslation();
    const { api } = useAuth();

    const [submitted, setSubmitted] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);

    const { control, handleSubmit, resetField } = useForm<RegisterForm>({
        defaultValues: { email: '' },
    });

    const doMagicLogin = async (data: RegisterForm) => {
        setSubmitted(false);
        setError(null);

        try {
            await api.auth.magicLoginRequest(data.email);
            setSubmitted(true)
        } catch (e) {
            resetField('email');

            if ((e as any).status === 429) {
                setError('login.magic_login.rate_limit')
                return;
            }

            setError('login.magic_login_callback.fail.desc_unknown');
        }
    };

    return <Form
        style={{ maxWidth: 250, marginTop: 16 }}
        onFinish={handleSubmit(doMagicLogin)}
    >
        <Flex vertical gap={16}>
            {
                !submitted && <>
                    <Typography.Text>{t('login.magic_login.desc')}</Typography.Text>

                    <FormItem control={control} name="email" style={{ marginBottom: 0 }}>
                        <Input prefix={<IconMail size={20} />} placeholder={t('generic.email')} required />
                    </FormItem>

                    {error && <Typography.Text className="red-glow">{t(error)}</Typography.Text>}

                    <Flex align="center" justify="center">
                        <Form.Item>
                            <Button
                                type="primary"
                                htmlType="submit"
                                icon={<IconLogin size={20} />}
                            >
                                {t('login.login_bt')}
                            </Button>
                        </Form.Item>
                    </Flex>
                </>
            }
            {submitted && error && <Typography.Text className="red-glow">{t(error)}</Typography.Text>}
            {submitted && !error && <Typography.Text className="blue-glow">{t('login.magic_login.sent')}</Typography.Text>}
        </Flex>
    </Form>;
}