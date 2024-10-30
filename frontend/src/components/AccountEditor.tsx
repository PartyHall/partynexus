import { Button, Flex, Form, Input, Select } from "antd";
import { FormItem } from "react-hook-form-antd";
import { User } from "../sdk/responses/user";
import { useForm } from "react-hook-form";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { useAuth } from "../hooks/auth";
import { ValidationErrors } from "../sdk/responses/validation_error";
import useNotification from "antd/es/notification/useNotification";

type Props = {
    user: User;
};

export default function AccountEditor({ user: initialUser }: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [user, setUser] = useState<User>(initialUser);
    const [notif, notifCtx] = useNotification();

    const { control, handleSubmit, setError, formState } = useForm<User>({
        defaultValues: {
            username: user.username,
            email: user.email,
            language: user.language,
        },
    });

    const doUpdateUser = async (data: User) => {
        try {
            const resp = await api.users.update(user.id, data);
            if (resp) {
                setUser(resp);
            }
        } catch (e) {
            if (e instanceof ValidationErrors) {
                // @ts-expect-error BECAUSE THIS FUCKING LANGUAGE SUCKS
                e.errors.forEach(x => setError(x.fieldName, {type: 'custom', 'message': x.getText()}));
                return;
            }

            console.error(e);
            notif.error({
                message: 'Unknown error occured',
                description: 'See console for more details',
            });
        }
    };

    return <Form
        style={{ width: 300 }}
        layout='vertical'
        onFinish={handleSubmit(doUpdateUser)}
    >
        <FormItem
            control={control}
            name="username"
            label={t('users.editor.username')}
        >
            <Input disabled={formState.isSubmitting} />
        </FormItem>

        <FormItem
            control={control}
            name="email"
            label={t('users.editor.email')}
        >
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
                    {t('users.editor.save')}
                </Button>
            </Form.Item>
        </Flex>

        {notifCtx}
    </Form>;
}