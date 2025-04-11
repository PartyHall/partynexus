import { Button, Modal, notification } from "antd";
import PasswordEditor from "./PasswordEditor";
import { User } from "../../sdk/responses/user";
import { useAuth } from "../../hooks/auth";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    passwordFormOpen: boolean;
    setPasswordFormOpen: (open: boolean) => void;
    user: User;
};

export default function PasswordEditorModal({
    passwordFormOpen,
    setPasswordFormOpen,
    user,
}: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [passwordChangeError, setPasswordChangeError] = useState<string>('');

    const [oldPassword, setOldPassword] = useState<string>('');
    const [newPassword, setNewPassword] = useState<string>('');
    const [isValid, setValid] = useState<boolean>(false);
    const [isSubmitting, setSubmitting] = useState<boolean>(false);

    const handleCancel = () => {
        setOldPassword('');
        setNewPassword('');
        setValid(false);
        setPasswordChangeError('');
        setSubmitting(false);

        setPasswordFormOpen(false);
    }

    const submit = async () => {
        setSubmitting(true);

        try {
            await api.users.setPassword(
                user.id,
                oldPassword.length > 0 ? oldPassword : null,
                newPassword,
            );

            notification.open({
                message: t('my_account.set_password.success_title'),
                description: t('my_account.set_password.success_desc'),
                type: 'success',
            });

            handleCancel();
        } catch (e: any) {
            console.error(e);

            if (e.status && e.status === 404) {
                setPasswordChangeError(t('login.magic_password.not_found'));
            } else if (e.message && e.message.detail) {
                setPasswordChangeError(e.message.detail);
            } else if (e.errors) {
                setPasswordChangeError(e.errors.flatMap((item: any) => item.errors || []));
            } else {
                setPasswordChangeError(t('generic.error.unknown'));
            }

            setSubmitting(false);
            setValid(false);
        }
    };

    return <Modal
        open={passwordFormOpen}
        onCancel={handleCancel}
        onOk={submit}
        footer={[
            <Button key="cancel" onClick={handleCancel}>{t('generic.cancel')}</Button>,
            <Button key="save" onClick={submit} disabled={!isValid || isSubmitting}>{t('generic.save')}</Button>,
        ]}
    >
        {
            // Fuck react
            // umount my fucking component so that it clears its internal state
            // this doesn't work
            // and I don't care enough of this shitty frontend that I should fully rewrite
            passwordFormOpen
            && <PasswordEditor
                requirePreviousPassword={user.isPasswordSet}
                onValueChanged={(oldPassword, newPassword, isValid) => {
                    setOldPassword(oldPassword);
                    setNewPassword(newPassword);
                    setValid(isValid);

                    setPasswordChangeError('');
                }}
                error={passwordChangeError}
            />
        }
    </Modal>
}