import { Card, Flex } from "antd";
import KeyVal from "../Keyval";
import { PnSongSession } from "../../sdk/responses/song";
import { useTranslation } from "react-i18next";

export default function SongSession({ session }: { session: PnSongSession }) {
    const { t } = useTranslation();

    return <Card>
        <Flex vertical>
            <KeyVal label={t('event.karaoke.session.title')}>{session.title}</KeyVal>
            <KeyVal label={t('event.karaoke.session.artist')}>{session.artist}</KeyVal>
            <KeyVal label={t('event.karaoke.session.sung_at')}>{session.sungAt.format('HH:mm DD/MM/YYYY')}</KeyVal>
            <KeyVal label={t('event.karaoke.session.singer')}>{session.singer}</KeyVal>
        </Flex>
    </Card>
}