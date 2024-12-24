import { AutoComplete, Flex, Modal, Typography } from 'antd';
import { PnEvent } from '../../sdk/responses/event';
import { useAuth } from '../../hooks/auth';
import { useDebounceFn } from 'ahooks';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type OptionType = {
    value: string;
    label: string;
};

type Props = {
    event: PnEvent;
    setEvent: (e: PnEvent) => void;
};

export default function AddParticipantToolbar({ event, setEvent }: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [notif, notifCtx] = useNotification();

    const [options, setOptions] = useState<OptionType[]>([]);
    const [searchText, setSearchText] = useState<string>('');

    const fetchSearch = async (query: string) => {
        if (!query) {
            setOptions([]);
            return;
        }

        try {
            const currentUsers = event.participants.map((x) => x.iri);

            const users = await api.users.getCollection(query);
            if (users) {
                setOptions([
                    ...users.items
                        .filter(
                            (x) =>
                                !currentUsers.includes(x.iri) &&
                                x.iri !== api.tokenUser?.iri
                        )
                        .map((x) => ({
                            label: x.username,
                            value: x.iri,
                        })),
                ]);
            }
        } catch (e) {
            notif.error({
                message: t('event.participants.search.failure.title'),
                description: t('event.participants.search.failure.description'),
            });

            console.error(e);
        }
    };

    const { run: debouncedSearch } = useDebounceFn(fetchSearch, { wait: 300 });

    // @TODO: Maybe we want to add a modal
    const addUser = async (userIri: string) => {
        if (!event || event.participants.map((x) => x.iri).includes(userIri)) {
            setSearchText('');
            return;
        }

        try {
            const resp = await api.events.updateParticipants(event, [
                ...event.participants.map((x) => x.iri),
                userIri,
            ]);

            if (resp) {
                setEvent(resp);
            }
        } catch (e) {
            notif.error({
                message: t('event.participants.add.failure.title'),
                description: t('event.participants.add.failure.description'),
            });

            console.error(e);
        }

        setSearchText('');
    };

    if (!api.tokenUser?.roles.includes('ROLE_ADMIN')) {
        return <></>;
    }

    return (
        <Flex align="center" justify="center" gap={8}>
            <Typography.Text>
                {t('event.participants.add.title')}:{' '}
            </Typography.Text>
            <AutoComplete
                placeholder={t('username')}
                style={{ minWidth: '100px' }}
                options={options}
                value={searchText}
                onClick={(evt) => evt.stopPropagation()}
                onSearch={(val) => {
                    setSearchText(val);
                    debouncedSearch(val);
                }}
                onChange={(val) => setSearchText(val)}
                onSelect={(val, ot) =>
                    Modal.confirm({
                        title: t('event.participants.add.title'),
                        content: t('event.participants.add.confirm', {
                            participant: ot.label,
                        }),
                        onOk: () => {
                            addUser(val);
                        },
                        onCancel: () => setSearchText(''),
                        okText: t('generic.modal_im_sure'),
                        cancelText: t('generic.cancel'),
                    })
                }
            />

            {notifCtx}
        </Flex>
    );
}
