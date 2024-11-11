import { AutoComplete, Button, Card, Flex, Popconfirm, Typography } from "antd";
import { IconUserX } from "@tabler/icons-react";
import { PnEvent } from "../../sdk/responses/event";
import { useAuth } from "../../hooks/auth";
import { useDebounceFn } from "ahooks";
import useNotification from "antd/es/notification/useNotification";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type OptionType = {
    value: string;
    label: string;
};

export default function ParticipantsEditor({ event, setEvent }: { event: PnEvent, setEvent: (e: PnEvent) => void }) {
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
            const currentUsers = event.participants.map(x => x.iri);

            const users = await api.users.getCollection(query);
            if (users) {
                setOptions([
                    ...users
                        .items
                        .filter(x => !currentUsers.includes(x.iri) && x.iri !== api.tokenUser?.iri)
                        .map(x => ({
                            label: x.username,
                            value: x.iri,
                        }))
                ]);
            }
        } catch (e) {
            notif.error({
                message: t('event.editor.search.failure.title'),
                description: t('event.editor.search.failure.description'),
            });

            console.error(e);
        }
    };

    const { run: debouncedSearch } = useDebounceFn(fetchSearch, { wait: 300 });

    // @TODO: Maybe we want to add a modal
    const addUser = async (userIri: string) => {
        if (!event || event.participants.map(x => x.iri).includes(userIri)) {
            setSearchText('');
            return;
        }

        try {
            const resp = await api.events.updateParticipants(
                event,
                [...event.participants.map(x => x.iri), userIri],
            );

            if (resp) {
                setEvent(resp);
            }
        } catch (e) {
            notif.error({
                message: t('event.editor.add.failure.title'),
                description: t('event.editor.add.failure.description'),
            });

            console.error(e);
        }

        setSearchText('');
    };

    const deleteUser = async (username: string, userIri: string) => {
        if (!event) {
            return;
        }

        try {
            const resp = await api.events.updateParticipants(
                event,
                event.participants
                    .map(x => x.iri)
                    .filter(x => x !== userIri)
            );

            if (resp) {
                setEvent(resp);
            }
        } catch (e) {
            notif.error({
                message: t('event.editor.delete.failure.title'),
                description: t('event.editor.delete.failure.description', { name: username }),
            });

            console.error(e);
        }
    }

    return <>
        <Typography.Title>{t('event.editor.participants')}</Typography.Title>
        <Flex gap={8} align="center">
            <Typography.Text>{t('event.editor.add.title')}:</Typography.Text>
            <AutoComplete
                style={{ flex: 1 }}
                options={options}
                value={searchText}
                onSearch={val => {
                    setSearchText(val);
                    debouncedSearch(val);
                }}
                onChange={val => setSearchText(val)}
                onSelect={val => addUser(val)}
            />
        </Flex>
        <Flex gap={8} wrap="wrap" align="center" justify="center">
            {
                event?.participants.sort((a, b) => a.username.localeCompare(b.username)).map(x => <Card className="EventEditor__Participant" key={x.iri}>
                    <Flex justify="space-between" align="center" gap={10}>
                        <p>{x.username}</p>
                        <Popconfirm
                            title={t('event.editor.delete.title')}
                            description={t('event.editor.delete.description', { name: x.username })}
                            onConfirm={() => deleteUser(x.username, x.iri)}
                            okText={t('event.editor.delete.yes')}
                            cancelText={t('event.editor.delete.cancel')}
                        >
                            <Button type="primary" shape="circle" icon={<IconUserX size={18} />} />
                        </Popconfirm>
                    </Flex>
                </Card>)
            }

            {notifCtx}
        </Flex>
    </>
}