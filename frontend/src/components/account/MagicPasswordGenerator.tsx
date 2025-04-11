import { Button, Flex, Modal, Typography } from 'antd';
import { MagicPassword, User } from '../../sdk/responses/user';
import CopyField from '../CopyField';
import { useAuth } from '../../hooks/auth';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Props = {
    user: User;
};

export default function MagicPasswordGenerator({ user }: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [notif, notifCtx] = useNotification();

    const [isGenerating, setIsGenerating] = useState<boolean>(false);
    const [generatedLink, setGeneratedLink] = useState<MagicPassword | null>(
        null
    );

    const generate = async () => {
        setIsGenerating(true);

        try {
            const link = await api.users.generateMagicPassword(user.id);
            if (!link) {
                throw 'Failed to generate magicpassword (unknown err)';
            }

            setGeneratedLink(link);
        } catch (e: any) {
            console.error(e);
            notif.error({
                message: t('generic.error.unknown'),
                description: t('generic.error.unknown_desc'),
            });
        }

        setIsGenerating(false);
    };

    return (
        <Flex vertical gap={8} align="center" justify="center">
            <Typography.Title className="blue-glow">
                {t('users.editor.magic_password.generator.title')}
            </Typography.Title>

            <Typography.Text>
                {t('users.editor.magic_password.generator.desc')}
            </Typography.Text>

            <Button disabled={isGenerating} onClick={generate}>
                {t('users.editor.magic_password.generator.generate')}
            </Button>

            <Modal
                title={t('users.editor.magic_password.generator.generate')}
                open={!!generatedLink}
                onCancel={() => setGeneratedLink(null)}
                footer={<></>}
            >
                <Typography.Text>
                    {t('users.editor.magic_password.generator.generated_desc')}
                </Typography.Text>
                <CopyField text={generatedLink?.url ?? ''} />
            </Modal>

            {notifCtx}
        </Flex>
    );
}
