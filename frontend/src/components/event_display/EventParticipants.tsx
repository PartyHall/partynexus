import { Button, Flex, List, Popconfirm } from 'antd';
import { IconCrown, IconUserX } from '@tabler/icons-react';
import { PnEvent } from '../../sdk/responses/event';
import getUsername from '../../utils/username';
import { useAuth } from '../../hooks/auth';
import useNotification from 'antd/es/notification/useNotification';
import { useTranslation } from 'react-i18next';

type Props = {
    event: PnEvent;
    setEvent: (evt: PnEvent) => void;
};

export default function EventParticipants({ event, setEvent }: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();

    const [notif, notifCtx] = useNotification();

    const participants = [event.owner, ...event.participants];

    const deleteUser = async (username: string, userIri: string) => {
        if (!event) {
            return;
        }

        try {
            const resp = await api.events.updateParticipants(
                event,
                event.participants
                    .map((x) => x.iri)
                    .filter((x) => x !== userIri)
            );

            if (resp) {
                setEvent(resp);
            }
        } catch (e) {
            notif.error({
                message: t('event.participants.delete.failure.title'),
                description: t(
                    'event.participants.delete.failure.description',
                    {
                        name: username,
                    }
                ),
            });

            console.error(e);
        }
    };

    return (
        <Flex vertical>
            <List
                itemLayout="horizontal"
                dataSource={participants}
                renderItem={(x) => (
                    <List.Item
                        actions={[
                            x.id !== event.owner.id ? (
                                <Popconfirm
                                    key="delete"
                                    title={t('event.participants.delete.title')}
                                    description={t(
                                        'event.participants.delete.description',
                                        { name: getUsername(x) }
                                    )}
                                    onConfirm={() =>
                                        deleteUser(x.username, x.iri)
                                    }
                                    okText={t('generic.modal_im_sure')}
                                    cancelText={t('generic.cancel')}
                                >
                                    <Button icon={<IconUserX size={18} />} />
                                </Popconfirm>
                            ) : (
                                <IconCrown
                                    size={18}
                                    className="icon-blue-glow"
                                />
                            ),
                        ]}
                    >
                        <List.Item.Meta
                            title={getUsername(x)}
                            description={x.username}
                        />
                    </List.Item>
                )}
            ></List>
            {notifCtx}
        </Flex>
    );
}
