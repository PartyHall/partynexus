import { Card, Flex, Tooltip, Typography } from "antd";
import { IconMicrophone, IconPiano, IconVinyl } from '@tabler/icons-react';
import { Link } from "react-router-dom";
import PnSong from "../sdk/responses/song";
import Title from "antd/es/typography/Title";
import { useTranslation } from "react-i18next";


export default function SongCard({ song }: { song: PnSong }) {
    const { t } = useTranslation();
    let url = 'https://placehold.co/64x64/171520/d72793/png';

    if (song.coverUrl) {
        url = song.coverUrl;
    }

    return <Card
        size="small"
    >
        <Flex gap={8}>
            <img
                style={{
                    width: 64,
                    height: 64,
                    objectFit: 'contain',
                }}
                src={url}
                alt={`${song.title} - ${song.artist}`}
            />

            <Flex vertical style={{ flex: '1' }}>
                <Typography.Text><Title level={2} style={{ margin: 0 }}>{song.title}</Title></Typography.Text>
                <Typography.Text>{t('karaoke.by', { artist: song.artist })}</Typography.Text>
                <Link to={`/karaoke/${song.id}`} style={{ fontSize: '.8em' }}>Edit</Link>
            </Flex>

            <Flex vertical gap={4}>
                <Tooltip title={t('karaoke.files.instrumental')}>
                    <IconPiano size={20} color="#fafa" />
                </Tooltip>
                <Tooltip title={t('karaoke.files.vocals')}>
                    <IconMicrophone size={20} color={song.vocals ? "#fafa" : "#777"} />
                </Tooltip>
                <Tooltip title={t('karaoke.files.mixed')}>
                    <IconVinyl size={20} color={song.full ? "#fafa" : "#777"} />
                </Tooltip>
            </Flex>
        </Flex>
    </Card>;
}