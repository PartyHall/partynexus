import { Button, Flex, Popconfirm } from "antd";
import { IconCheckbox, IconCloudDownload, IconFileArrowRight } from "@tabler/icons-react";
import { PnEvent } from "../../sdk/responses/event";
import { useAuth } from "../../hooks/auth";
import useNotification from "antd/es/notification/useNotification";
import { useTranslation } from "react-i18next";

type Props = {
    event: PnEvent;
    displayOwnerStuff: boolean;
    setEvent: (e: PnEvent|null) => void;
};

export default function EventActionBar({ event, displayOwnerStuff, setEvent }: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [notif, notifCtx] = useNotification();

    const onDownload = () => {
        const link = document.createElement('a');
        link.href = `/api/events/${event.id}/export`;
        link.download = `${event.id}.zip`;
        document.body.appendChild(link);
        link.click();
        link.remove();
    };

    const onConclude = async () => {
        try {
            const newEvent = await api.events.conclude(event);
            notif.success({
                message: t('event.conclude.success.title'),
                description: t('event.conclude.success.description'),
            });

            setEvent(newEvent);
        } catch (e) {
            notif.error({
                message: t('event.conclude.failed.title'),
                description: t('event.conclude.failed.description'),
            })

            console.error(e);
        }
    };

    return <Flex align="center" justify="space-around" style={{ marginTop: 8 }}>
        {
            displayOwnerStuff &&
            !event.over &&
            <Popconfirm
                title={t('event.conclude.title')}
                description={t('event.conclude.description')}
                onConfirm={onConclude}
                okText={t('generic.modal_im_sure')}
                cancelText={t('generic.cancel')}
            >
                <Button icon={<IconCheckbox />}>{t('event.conclude.bt')}</Button>
            </Popconfirm>
        }
        {
            displayOwnerStuff &&
            event.over &&
            <Button icon={<IconFileArrowRight />} onClick={onConclude}>{t('event.force_export')}</Button>
        }
        {
            event.export &&
            <Button icon={<IconCloudDownload />} onClick={onDownload}>
                {t('event.download_export')}
            </Button>
        }

        {notifCtx}
    </Flex>
}