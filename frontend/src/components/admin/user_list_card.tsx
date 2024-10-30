import { Ban, Undo2 } from "lucide-react";
import { Button, Card, Flex, Popconfirm, Tooltip, Typography } from "antd";
import { PnListUser } from "../../sdk/responses/user";
import { useAuth } from "../../hooks/auth";
import useNotification from "antd/es/notification/useNotification";
import { useState } from "react";
import { useTranslation } from "react-i18next";

export default function UserListCard({ user: listUser }: { user: PnListUser }) {
    const [user, setUser] = useState<PnListUser>(listUser);

    const { api } = useAuth();
    const [notif, notifCtx] = useNotification();
    const { t } = useTranslation();

    const banUser = async (username: string, iri: string) => {
        try {
            const newUser = await api.users.ban(iri);
            notif.success({
                message: t('users.ban.success_title'),
                description: t('users.ban.success_title', {username}),
            });

            if (newUser) {
                setUser(newUser);
            }
        } catch (e) {
            notif.error({
                message: t('users.ban.error_title'),
                description: t('users.ban.error_title', {username}),
            });

            console.error(e);
        }
    };

    const unbanUser = async (username: string, iri: string) => {
        try {
            const newUser = await api.users.unban(iri);
            notif.success({
                message: t('users.unban.success_title'),
                description: t('users.unban.success_title', {username}),
            });

            if (newUser) {
                setUser(newUser);
            }
        } catch (e) {
            notif.error({
                message: t('users.unban.error_title'),
                description: t('users.unban.error_title', {username}),
            });

            console.error(e);
        }
    };

    return <Card>
        <Flex align="center" justify="space-between">
            <Flex vertical>
                <Typography.Title style={{ fontSize: '1.2em', margin: 0 }}>{user.username}</Typography.Title>
                <Typography.Text>{user.email}</Typography.Text>
            </Flex>

            {
                (api.tokenUser?.username !== user.username) && user.bannedAt && <Popconfirm
                    title={t('users.unban.confirm.title', { username: user.username })}
                    description={t('users.unban.confirm.desc')}
                    onConfirm={() => unbanUser(user.username, user.iri)}
                    okText={t('users.unban.confirm.yes')}
                    cancelText={t('users.unban.confirm.cancel')}
                >
                    <Flex align="center" justify="center" gap={16}>
                        <Typography.Text>{user.bannedAt.format('YYYY-MM-DD HH:mm:ss')}</Typography.Text>
                        <Tooltip title={t('users.unban.tooltip')}>
                            <Button icon={<Undo2 />} />
                        </Tooltip>
                    </Flex>
                </Popconfirm>
            }

            {
                (api.tokenUser?.username !== user.username) && !user.bannedAt && <Popconfirm
                    title={t('users.ban.confirm.title', { username: user.username })}
                    description={t('users.ban.confirm.desc')}
                    onConfirm={() => banUser(user.username, user.iri)}
                    okText={t('users.ban.confirm.yes')}
                    cancelText={t('users.ban.confirm.cancel')}
                >
                    <Tooltip title={t('users.ban.tooltip')}>
                        <Button icon={<Ban />} />
                    </Tooltip>
                </Popconfirm>
            }
        </Flex>

        {notifCtx}
    </Card>
}