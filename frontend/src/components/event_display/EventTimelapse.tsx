import { PnEvent } from "../../sdk/responses/event";
import { Typography } from "antd";

import { useTranslation } from "react-i18next";

export default function EventTimelapse({ event }: { event: PnEvent }) {
    const {t} = useTranslation();

    return <>
        {
            /* Only if the event is finished & the timelapse is generated */

            event.over &&
            <Typography.Title>{t('event.pictures.timelapse')}</Typography.Title>
        }
    </>
}