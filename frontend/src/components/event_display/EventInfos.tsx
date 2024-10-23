import { Flex, Typography } from "antd";
import KeyVal from "../Keyval";
import { PnEvent } from "../../sdk/responses/event";
import { useTranslation } from "react-i18next";

type Props = {
    event: PnEvent;
    displayOwnerStuff: boolean;
};

export default function EventInfos({ event, displayOwnerStuff }: Props) {
    const { t } = useTranslation();
    return <>
        {
            displayOwnerStuff &&
            <Typography.Text>Note: If the event was created manually, the id is to be filled on every appliance participating to the event.</Typography.Text>
        }
        <Flex vertical>
            {
                displayOwnerStuff &&
                <KeyVal label={t('event.id')}>{event?.id}</KeyVal>
            }
            <KeyVal label={t('event.author')}>{event?.author}</KeyVal>
            <KeyVal label={t('event.datetime')}>{event?.datetime.format('HH:mm - DD / MM / YYYY')}</KeyVal>
            <KeyVal label={t('event.location')}>{event?.location}</KeyVal>
        </Flex>
    </>;
}