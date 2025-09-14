import { IconCheck } from "@tabler/icons-react";
import { Tooltip } from "../generic/tooltip";
import { useTranslation } from "react-i18next";
import type { ExternalSong } from "@/types/karaoke";
import Button from "../generic/button";
import Card from "../generic/card";

type Props = {
    song: ExternalSong;
    onSelect: () => void;
};

export default function ExternalSongCard({ song, onSelect }: Props) {
    const { t } = useTranslation();

    return (
        <Card className="songCard" noGlow>
            {
                song.cover && song.cover
                && <img
                    src={song.cover ?? 'https://placehold.co/64x64/171520/d72793/png'}
                    alt={`${song.title} cover`}
                    className="block h-20 rounded-2xl"
                    onError={e => {
                        const target = e.currentTarget;
                        target.src = 'https://placehold.co/64x64/171520/d72793/png';
                    }}
                />
            }
            <div className='songDetails'>
                <h3>{song.title}</h3>
                <p className="text-gray-500">{song.artist}</p>
                <p className="text-gray-500">{song.id}</p>
            </div>

            <div className="songFiles">
                <Tooltip content={t('generic.use')}>
                    <Button onClick={onSelect}>
                        <IconCheck size={20} />
                    </Button>
                </Tooltip>
            </div>
        </Card>
    );
}