import { Button, Card, Flex, Form, Input, Select, Typography } from "antd";

import { FormItem } from "react-hook-form-antd";
import { ValidationErrors } from "../../sdk/responses/validation_error";

import { useAuth } from "../../hooks/auth";
import { useForm } from "react-hook-form";
import useNotification from "antd/es/notification/useNotification";
import { useTitle } from "ahooks";
import { useTranslation } from "react-i18next";

type UserCreationProps = {
    username: string;
    email: string;
    language: string;
};

export default function AdminNewUserPage() {
    const [notif, notifCtx] = useNotification();
    const { t } = useTranslation();
    const {api} = useAuth();

    useTitle(`${t('users.new_user_bt')} - PartyHall`)

    const { control, formState, handleSubmit, setError } = useForm<UserCreationProps>({
        defaultValues: {
            username: '',
            email: '',
            language: 'en_US',
        },
    });

    const doCreateUser = async (data: UserCreationProps) => {
        try {
            const user = await api.users.register(data.username, data.email, data.language);
            notif.success({
                message: t('users.new.success.title'),
                description: t('users.new.success.desc', {'username': user?.username ?? data.username}),
            })
        } catch (e: any) {
            if (e instanceof ValidationErrors) {
                // @ts-expect-error BECAUSE THIS FUCKING LANGUAGE SUCKS
                e.errors.forEach(x => setError(x.fieldName, {type: 'custom', 'message': x.getText()}));
                return;
            }
            
            notif.error({
                message: t('generic.error.unknown'),
                description: t('generic.error.unknown_desc'),
            });

            console.error(e);
        }
    }

    return <Flex vertical align="center" justify="center" style={{ flex: 1 }} gap={16}>
        <Typography.Title className="blue-glow">{t('users.new_user_bt')}</Typography.Title>

        <Card>
            <Form
                layout="vertical"
                onFinish={handleSubmit(doCreateUser)}
            >
                <FormItem control={control} name="username" label={t('users.new.username')}>
                    <Input disabled={formState.isSubmitting} />
                </FormItem>

                <FormItem control={control} name="email" label={t('users.new.email')}>
                    <Input disabled={formState.isSubmitting} />
                </FormItem>

                <FormItem control={control} name="language" label={t('users.new.lang')}>
                    <Select
                        disabled={formState.isSubmitting}
                        options={[
                            { value: 'en_US', label: 'English (American)' },
                            { value: 'fr_FR', label: 'Français' },
                        ]}
                    />
                </FormItem>

                <Flex align="center" justify="center" style={{ marginTop: 32 }}>
                    <Form.Item>
                        <Button type="primary" htmlType="submit" disabled={formState.isSubmitting}>
                            {t('users.new.register')}
                        </Button>
                    </Form.Item>
                </Flex>
            </Form>
        </Card>
        {notifCtx}
    </Flex>;
}