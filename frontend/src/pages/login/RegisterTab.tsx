import { Button, Flex, Form, Input } from "antd";
import { LockOutlined, MailOutlined, UserOutlined } from '@ant-design/icons';
import { FormItem } from "react-hook-form-antd";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

type RegisterForm = {
    username: string;
    password: string;
    password2: string;
    email: string;
};

export default function RegisterTab() {
    const {t} = useTranslation();

    // const { login } = useAuth();
    const { control, handleSubmit } = useForm<RegisterForm>({
        defaultValues: { username: '', password: '' },
    });

    const doRegister = async (data: RegisterForm) => {
        console.log(data);
        // await login(data.username, data.password)
    };

    return <Form
        style={{ maxWidth: 600, marginTop: 16 }}
        onFinish={handleSubmit(doRegister)}
    >
        <FormItem control={control} name="username">
            <Input prefix={<UserOutlined />} placeholder={t('generic.username')} required />
        </FormItem>

        {/* @TODO: validate email before sending the request */}
        <FormItem control={control} name="email">
            <Input prefix={<MailOutlined />} placeholder={t('generic.email')} required />
        </FormItem>

        <FormItem control={control} name="password">
            <Input.Password prefix={<LockOutlined />} placeholder={t('generic.password')} required />
        </FormItem>

        <FormItem control={control} name="password2">
            <Input.Password prefix={<LockOutlined />} placeholder={t('login.retype_password')} required />
        </FormItem>

        <Flex align="center" justify="center" style={{marginTop: 32}}>
            <Form.Item>
                <Button type="primary" htmlType="submit">
                    {t('login.register_bt')}
                </Button>
            </Form.Item>
        </Flex>
    </Form>;
}