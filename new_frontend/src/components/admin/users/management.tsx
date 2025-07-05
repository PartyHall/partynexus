import Button from "@/components/generic/button";
import { CopyInput } from "@/components/generic/input";
import Title from "@/components/generic/title";
import { useAuthStore } from "@/stores/auth";
import type { User } from "@/types/user";
import dayjs from "dayjs";
import { enqueueSnackbar } from "notistack";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { generateMagicPassword as apiGenerateMagicPassword, banUser, unbanUser } from '@/api/users/management';

type Props = {
    user: User;
    onUpdated?: (user: User) => void;
};

export default function jUserManagement({ user, onUpdated }: Props) {
    const { t } = useTranslation();
    const tokenUser = useAuthStore((state) => state.tokenUser);

    const [isBanning, setIsBanning] = useState<boolean>(false);

    const [generatingMagicPassword, setGeneratingMagicPassword] = useState<boolean>(false);
    const [generatedMagicPassword, setGeneratedMagicPassword] = useState<string | null>(null);

    const generateMagicPassword = async () => {
        if (!user.id) {
            return;
        }
        setGeneratingMagicPassword(true);

        try {
            const resp = await apiGenerateMagicPassword(user.id);

            setGeneratedMagicPassword(resp.url);
        } catch (err) {
            console.error('Error generating magic password:', err);
            enqueueSnackbar(t('generic.error.generic'), { variant: 'error' });
            setGeneratedMagicPassword(null);
        }

        setGeneratingMagicPassword(false);
    };

    const toggleBan = async () => {
        if (tokenUser?.id === user.id) {
            return;
        }

        setIsBanning(true);

        try {
            let newUser = null;
            if (user.bannedAt === null) {
                newUser = await banUser(user.id);
            } else {
                newUser = await unbanUser(user.id);
            }

            onUpdated?.(newUser);
        } catch (err) {
            console.error('Error toggling ban:', err);
            enqueueSnackbar(t('generic.error.generic'), { variant: 'error' });
        }

        setIsBanning(false);
    };

    return <div className="flex flex-col gap-4">
        <div className="flex flex-col gap-2">
            <Title level={3} noMargin>{t('admin.users.management.magic_password.title')}</Title>
            <p className="text-primary-100">{t('admin.users.management.magic_password.desc')}</p>

            {
                generatedMagicPassword
                && <CopyInput value={generatedMagicPassword} label={t('admin.users.management.magic_password.title')} />
            }

            <div className="flex flex-row justify-end">
                <Button disabled={generatingMagicPassword} onClick={generateMagicPassword}>{t('admin.users.management.magic_password.generate')}</Button>
            </div>
        </div>

        {
            tokenUser?.id !== user.id
            && <div className="flex flex-col gap-2">
                <Title level={3} noMargin>{t('admin.users.management.ban.title')}</Title>
                <p className="text-primary-100">{t('admin.users.management.ban.desc')}</p>

                {
                    user.bannedAt !== null
                    && <p className="text-red-glow text-center">
                        {
                            t('admin.users.management.ban.banned_at', {
                                date: dayjs(user.bannedAt).format('L'),
                                time: dayjs(user.bannedAt).format('LT'),
                            })
                        }
                    </p>
                }

                <div className="flex flex-row justify-end">
                    <Button disabled={isBanning} onClick={toggleBan}>
                        {t(`admin.users.management.ban.${user.bannedAt === null ? 'title' : 'unban'}`)}
                    </Button>
                </div>
            </div>
        }
    </div>
}