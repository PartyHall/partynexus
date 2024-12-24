import { Button, Card, Flex, Popconfirm, Tooltip, Typography } from 'antd';
import { IconArrowBackUp, IconBan } from '@tabler/icons-react';
import { PnListUser } from '../../sdk/responses/user';
import getUsername from '../../utils/username';
import { useAuth } from '../../hooks/auth';
import { useNavigate } from 'react-router-dom';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function UserListCard({ user: listUser }: { user: PnListUser }) {
    const [user, setUser] = useState<PnListUser>(listUser);
    const navigate = useNavigate();

    const { api } = useAuth();
    const [notif, notifCtx] = useNotification();
    const { t } = useTranslation();

    const banUser = async (username: string, iri: string) => {
        try {
            const newUser = await api.users.ban(iri);
            notif.success({
                message: t('users.ban.success_title'),
                description: t('users.ban.success_title', { username }),
            });

            if (newUser) {
                setUser(newUser);
            }
        } catch (e) {
            notif.error({
                message: t('users.ban.error_title'),
                description: t('users.ban.error_title', { username }),
            });

            console.error(e);
        }
    };

    const unbanUser = async (username: string, iri: string) => {
        try {
            const newUser = await api.users.unban(iri);
            notif.success({
                message: t('users.unban.success_title'),
                description: t('users.unban.success_title', { username }),
            });

            if (newUser) {
                setUser(newUser);
            }
        } catch (e) {
            notif.error({
                message: t('users.unban.error_title'),
                description: t('users.unban.error_title', { username }),
            });

            console.error(e);
        }
    };

    return (
        <Card>
            <Flex align="center" justify="space-between">
                <Flex
                    vertical
                    onClick={() => {
                        navigate(`/admin/users/${user.id}`);
                    }}
                >
                    <Typography.Title
                        style={{ fontSize: '1.2em', margin: 0 }}
                        className="red-glow"
                    >
                        {getUsername(user)}
                    </Typography.Title>
                    <Typography.Text>
                        {user.username} ({user.email})
                    </Typography.Text>
                </Flex>

                {api.tokenUser?.username !== user.username && user.bannedAt && (
                    <Popconfirm
                        title={t('users.unban.confirm.title', {
                            username: user.username,
                        })}
                        description={t('users.unban.confirm.desc')}
                        onConfirm={(e) => {
                            unbanUser(user.username, user.iri);
                            e?.stopPropagation();
                        }}
                        onCancel={(e) => e?.stopPropagation()}
                        okText={t('generic.modal_im_sure')}
                        cancelText={t('generic.cancel')}
                        onPopupClick={(e) => e.stopPropagation()}
                    >
                        <Flex
                            align="center"
                            justify="center"
                            gap={16}
                            onClick={(e) => e.stopPropagation()}
                        >
                            <Typography.Text>
                                {user.bannedAt.format('YYYY-MM-DD HH:mm:ss')}
                            </Typography.Text>
                            <Tooltip title={t('users.unban.tooltip')}>
                                <Button icon={<IconArrowBackUp size={20} />} />
                            </Tooltip>
                        </Flex>
                    </Popconfirm>
                )}

                {api.tokenUser?.username !== user.username &&
                    !user.bannedAt && (
                        <Popconfirm
                            title={t('users.ban.confirm.title', {
                                username: user.username,
                            })}
                            description={t('users.ban.confirm.desc')}
                            onConfirm={(e) => {
                                banUser(user.username, user.iri);
                                e?.stopPropagation();
                            }}
                            onCancel={(e) => e?.stopPropagation()}
                            okText={t('generic.modal_im_sure')}
                            cancelText={t('generic.cancel')}
                            onPopupClick={(e) => e.stopPropagation()}
                        >
                            <Tooltip title={t('users.ban.tooltip')}>
                                <Button
                                    icon={<IconBan size={20} />}
                                    onClick={(e) => e.stopPropagation()}
                                />
                            </Tooltip>
                        </Popconfirm>
                    )}
            </Flex>

            {notifCtx}
        </Card>
    );
}
