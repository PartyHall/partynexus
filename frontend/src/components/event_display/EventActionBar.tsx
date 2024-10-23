import { Button, Flex } from "antd";
import { DownloadOutlined } from '@ant-design/icons';
import { PnEvent } from "../../sdk/responses/event";
import { useTranslation } from "react-i18next";

type Props = {
    event: PnEvent;
    displayOwnerStuff: boolean;
};

export default function EventActionBar({ event, displayOwnerStuff }: Props) {
    const { t } = useTranslation();

    /* If export generated: Add a button to download the export.zip */
    return <Flex align="center" justify="space-around" style={{ marginTop: 32 }}>
        {
            displayOwnerStuff &&
            <Button href={`/events/${event.id}/exports`}>{t('event.export_bt')}</Button>
        }
        {/* Maybe we should use the Mercure JWT to authenticate for the download as its already sent as cookie (?) */}
        <Button icon={<DownloadOutlined />}>{t('event.download_export')}</Button>
    </Flex>
}