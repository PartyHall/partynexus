import { Card, Flex, Typography } from "antd";
import { AudioLines } from "lucide-react";
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

    const getIconColor = (type: string) => {
        if (song.files && type == 'instrumental') {
            return '#FFF';
        }

        return '#000';
    };

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

            <Flex vertical style={{flex: '1'}}>
                <Typography.Text><Title level={2} style={{margin: 0}}>{song.title}</Title></Typography.Text>
                <Typography.Text>{t('karaoke.by', { artist: song.artist })}</Typography.Text>
                <Link to={`/karaoke/${song.id}`} style={{fontSize: '.8em'}}>Edit</Link>
            </Flex>

            <Flex vertical>
                <AudioLines color={getIconColor('instrumental')} />
            </Flex>
        </Flex>
    </Card>;
}