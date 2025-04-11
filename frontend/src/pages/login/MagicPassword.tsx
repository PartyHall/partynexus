import { Button, Card, Flex, Spin, Typography } from 'antd';
import { MagicPassword } from '../../sdk/responses/user';
import PasswordEditor from '../../components/account/PasswordEditor';
import { useAsyncEffect } from 'ahooks';
import { useAuth } from '../../hooks/auth';
import { Link, useParams } from 'react-router-dom';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function MagicPasswordPage() {
    const { code } = useParams();
    const { t } = useTranslation();
    const { api } = useAuth();

    const [success, setSuccess] = useState<boolean>(false);
    const [codeError, setCodeError] = useState<string>('');
    const [validationError, setValidationError] = useState<string>('');

    const [isSubmitting, setSubmitting] = useState<boolean>(false);
    const [magicPassword, setMagicPassword] = useState<MagicPassword | null>(
        null
    );
    const [newPassword, setNewPassword] = useState<string | null>(null);
    const [isValid, setIsValid] = useState<boolean>(false);

    useAsyncEffect(async () => {
        if (!code) {
            return;
        }

        try {
            setMagicPassword(await api.users.magicPasswordIsValid(code));
        } catch (e: any) {
            console.log(e);

            if (e.status && e.status === 404) {
                setCodeError(t('login.magic_password.not_found'));
            } else if (e.message && e.message.detail) {
                setCodeError(e.message.detail);
            } else if (e.errors) {
                setCodeError(e.errors.flatMap((item: any) => item.errors || []));
            } else {
                setCodeError(t('generic.error.unknown'));
            }
        }
    }, [code]);

    const submit = async () => {
        if (!code || !newPassword) {
            return;
        }

        setSubmitting(true);

        try {
            await api.users.magicPasswordSet(code, newPassword);
            setSuccess(true);
        } catch (e: any) {
            console.log(e);

            if (e.status && e.status === 404) {
                setValidationError(t('login.magic_password.not_found'));
            } else if (e.message && e.message.detail) {
                setValidationError(e.message.detail);
            } else if (e.errors) {
                setValidationError(e.errors.flatMap((item: any) => item.errors || []));
            } else {
                setValidationError(t('generic.error.unknown'));
            }
        }

        setSubmitting(false);
    };

    return (
        <Flex align="center" justify="center" style={{ height: '100%' }}>
            <Card>
                {
                    success
                    && <Flex vertical gap={8} align='center' justify='center'>
                        <Typography.Title className='blue-glow'>{t('login.magic_password.success_title')}</Typography.Title>
                        <Typography.Text>{t('login.magic_password.success_desc', {username: magicPassword?.user.username})}</Typography.Text>
                        <Link to='/login'>{t('login.magic_password.success_gohome')}</Link>
                    </Flex>
                }

                {
                    !success && codeError
                    && <Typography.Text className='red-glow'>
                        {codeError}
                    </Typography.Text>
                }

                {
                    !success && !codeError
                    && <Spin spinning={!magicPassword}>
                        <Flex vertical gap={8}>
                            <Typography.Text>
                                {t('login.magic_password.hello', {
                                    firstName: magicPassword?.user.firstname,
                                    lastName: magicPassword?.user.lastname,
                                })}
                                .
                            </Typography.Text>

                            <Typography.Text>
                                {t('login.magic_password.desc')}
                            </Typography.Text>

                            <PasswordEditor
                                onValueChanged={(_old, newPassword, isValid) => {
                                    setNewPassword(newPassword);
                                    setIsValid(isValid);
                                }}
                                error={validationError}
                            />

                            <Button
                                style={{ marginTop: 8 }}
                                disabled={isSubmitting || !isValid}
                                onClick={submit}
                            >
                                {t('generic.save')}
                            </Button>
                        </Flex>
                    </Spin>
                }
            </Card>
        </Flex>
    );
}
