import { Flex, Input, Typography } from 'antd';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

const passwordRegex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{8,}$/;

type Props = {
    requirePreviousPassword?: boolean;
    onValueChanged: (
        oldPassword: string,
        newPassword: string,
        isValid: boolean
    ) => void;
    error?: string | null;
};

export default function PasswordEditor({
    requirePreviousPassword,
    onValueChanged,
    error,
}: Props) {
    const { t } = useTranslation();

    const [oldPassword, setOldPassword] = useState<string>('');
    const [newPassword, setNewPassword] = useState<string>('');
    const [newPassword2, setNewPassword2] = useState<string>('');

    const [matches, setMatches] = useState<boolean>(false);

    useEffect(() => {
        const matches = newPassword === newPassword2 && newPassword.length > 0;

        setMatches(matches);

        let valid = matches;

        if (requirePreviousPassword) {
            valid = valid && oldPassword.length > 0;
        }

        if (!passwordRegex.test(newPassword)) {
            valid = false;
        }

        onValueChanged(oldPassword, newPassword, valid);
    }, [oldPassword, newPassword, newPassword2]);

    useEffect(() => {
        return () => {
            // That crap doesn't work
            // because for some stupid reason
            // the component never dismount
            // even though its display is conditionned
            setOldPassword('');
            setNewPassword('');
            setNewPassword2('');
            setMatches(false);
        };
    }, []);

    return (
        <Flex vertical gap={2}>
            {requirePreviousPassword && (
                <>
                    <Typography.Text>
                        {t('my_account.set_password.old_password')}:
                    </Typography.Text>
                    <Input.Password
                        size="small"
                        value={oldPassword}
                        onChange={(x) => setOldPassword(x.target.value)}
                    />
                </>
            )}

            <Typography.Text>
                {t('my_account.set_password.password')}:
            </Typography.Text>
            <Input.Password
                size="small"
                value={newPassword}
                onChange={(x) => setNewPassword(x.target.value)}
            />

            <Typography.Text>
                {t('my_account.set_password.password_2')}:
            </Typography.Text>
            <Input.Password
                size="small"
                value={newPassword2}
                onChange={(x) => setNewPassword2(x.target.value)}
            />

            {(error ||
                (!matches &&
                    (newPassword.length > 0 || newPassword2.length > 0))) && (
                <Flex
                    align="center"
                    justify="center"
                    style={{ marginTop: 16, marginBottom: 16 }}
                >
                    <Typography.Text className="red-glow">
                        {(error?.length ?? 0) > 0 ? error : t('my_account.set_password.dont_match')}
                    </Typography.Text>
                </Flex>
            )}

            <Typography.Text>
                {t('my_account.set_password.requirements')}:
            </Typography.Text>
            <ul>
                <li>{t('my_account.set_password.min_chars')}</li>
                <li>{t('my_account.set_password.luc')}</li>
                <li>{t('my_account.set_password.numbers')}</li>
                <li>{t('my_account.set_password.sc')}</li>
            </ul>
        </Flex>
    );
}
