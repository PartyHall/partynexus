import { Flex, List, Typography } from 'antd';
import { User, UserAuthenticationLog } from '../../sdk/responses/user';
import { useAsyncEffect, useTitle } from 'ahooks';

import AccountEditor from '../../components/account/AccountEditor';
import { Collection } from '../../sdk/responses/collection';
import KeyVal from '../../components/Keyval';
import Loader from '../../components/Loader';

import { useAuth } from '../../hooks/auth';
import { useParams } from 'react-router-dom';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function AdminEditUserPage() {
    const { id } = useParams();
    const { api } = useAuth();
    const { t } = useTranslation();

    const [user, setUser] = useState<User | null>(null);
    const [authLogs, setAuthLogs] =
        useState<Collection<UserAuthenticationLog> | null>(null);
    const [loading, setLoading] = useState<boolean>(true);

    useTitle(
        `${t('users.editor.edit_someone', { username: user?.username })} - PartyHall`
    );

    useAsyncEffect(async () => {
        if (!id) {
            return;
        }

        const resp = await api.users.getFromIri(`/api/users/${id}`);
        setUser(resp);

        if (!resp?.id) {
            setLoading(false);
            return;
        }

        try {
            const logs = await api.users.getAuthenticationLogs(resp.id);
            if (logs) {
                setAuthLogs(logs);
            }
        } catch (e) {
            console.error(e);
        }

        setLoading(false);
    }, [id]);

    return (
        <Flex
            vertical
            align="center"
            justify="center"
            style={{ flex: 1 }}
            gap={16}
        >
            <Loader loading={loading}>
                <Typography.Title className="blue-glow">
                    {t('users.editor.edit_someone', {
                        username: user?.username,
                    })}
                </Typography.Title>

                {user && <AccountEditor user={user} />}

                {authLogs && (
                    <>
                        <Typography.Title className="blue-glow">
                            {t('users.editor.authentication_logs')}
                        </Typography.Title>

                        <List
                            size="small"
                            bordered
                            dataSource={authLogs.items}
                            renderItem={(item) => (
                                <List.Item>
                                    <Flex vertical>
                                        <KeyVal label="IP">{item.ip}</KeyVal>
                                        <KeyVal label="Timestamp">
                                            {item.authedAt.toISOString()}
                                        </KeyVal>
                                    </Flex>
                                </List.Item>
                            )}
                        />
                    </>
                )}
            </Loader>
        </Flex>
    );
}
