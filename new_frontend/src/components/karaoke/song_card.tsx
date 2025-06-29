import { IconMicrophone, IconPiano, IconVinyl } from "@tabler/icons-react";
import { Tooltip } from "../generic/tooltip";
import { useTranslation } from "react-i18next";
import type { Song } from "@/types/karaoke";
import { Link } from "@tanstack/react-router";
import { useAuthStore } from "@/stores/auth";
import Card from "../generic/card";

type Props = { song: Song };

export default function SongCard({ song }: Props) {
    const { isGranted } = useAuthStore();
    const { t } = useTranslation();

    return (
        <Card className="songCard" noGlow>
            {
                <div className="flex flex-col align-center justify-center">
                    <img
                        src={song.coverUrl ?? 'https://placehold.co/64x64/171520/d72793/png'}
                        alt={`${song.title} cover`}
                        onError={e => {
                            const target = e.currentTarget;
                            target.src = 'https://placehold.co/64x64/171520/d72793/png';
                        }}
                    />
                </div>
            }
            <div className='songDetails'>
                <h3>{song.title}</h3>
                <p className="text-gray-500">{song.artist}</p>
                {
                    isGranted('ROLE_ADMIN')
                    && <div>
                        <Link to="/karaoke/$id" params={{ id: '' + song.id }}>
                            {t('generic.edit')}
                        </Link>
                    </div>
                }
            </div>

            <div className="songFiles">
                <Tooltip content={t('karaoke.files.instrumental')}>
                    <IconPiano size={20} color="#fafa" />
                </Tooltip>
                <Tooltip content={t('karaoke.files.vocals')}>
                    <IconMicrophone
                        size={20}
                        color={song.vocals ? '#fafa' : '#777'}
                    />
                </Tooltip>
                <Tooltip content={t('karaoke.files.mixed')}>
                    <IconVinyl
                        size={20}
                        color={song.combined ? '#fafa' : '#777'}
                    />
                </Tooltip>
            </div>
        </Card>
    );
}