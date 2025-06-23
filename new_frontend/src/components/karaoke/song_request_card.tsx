import { useTranslation } from "react-i18next";
import type { SongRequest } from "@/types/karaoke";
import Username from "../username";

type Props = { song: SongRequest };

export default function SongRequestCard({ song }: Props) {
    const { t } = useTranslation();

    return <div className="songCard">
        <div className='songDetails'>
            <h3>{t('karaoke.song_title')}: {song.title}</h3>
            <p className="text-gray-500">{t('karaoke.song_artist')}: {song.artist}</p>
            <Username
                user={song.requestedBy}
                prefix={<span className="text-gray-500 text-shadow-none">
                    {t('karaoke.request_song.requested_by')}:
                </span>}
            />
        </div>
    </div>;
}