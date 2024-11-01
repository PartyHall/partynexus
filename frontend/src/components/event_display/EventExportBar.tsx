import { Flex } from "antd";
import KeyVal from "../Keyval";
import PnExport from "../../sdk/responses/export";
import { useTranslation } from "react-i18next";

type Props = {
    pnExport: PnExport;
}

export default function EventExportBar({ pnExport }: Props) {
    const { t } = useTranslation();

    return <Flex vertical>
        <KeyVal label={t('event.export.id')}>{pnExport.id}</KeyVal>
        <KeyVal label={t('event.export.started_at')}>{pnExport.startedAt.format('HH:mm:ss - DD / MM / YYYY')}</KeyVal>
        {
            pnExport.endedAt &&
            <KeyVal label={t('event.export.ended_at')}>{pnExport.endedAt.format('HH:mm:ss - DD / MM / YYYY')}</KeyVal>
        }
        <KeyVal label={t('event.export.progress')}>{pnExport.progress}</KeyVal>
        <KeyVal label={t('event.export.status')}>{pnExport.status}</KeyVal>
    </Flex>;
}