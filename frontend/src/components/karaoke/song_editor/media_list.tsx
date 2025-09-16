import Card from "@/components/generic/card";
import Title from "@/components/generic/title";
import { useTranslation } from "react-i18next";
import UploadMediaButton from "./upload_media_button";
import type { Song } from "@/types/karaoke";

type Props = {
    song: Song;
    type: 'VIDEO' | 'TRANSPARENT_VIDEO' | 'INSTRUMENTAL' | 'VOCALS' | 'FULL' | 'CDG';
    hasAnUpload: boolean;
    fileUrl: string | null;
    onMediaUploaded?: () => void;
};

export default function MediaList({ song, type, hasAnUpload, fileUrl, onMediaUploaded }: Props) {
    const { t } = useTranslation();

    return <Card className="flex flex-col gap-2 items-center">
        <Title level={3} className="mb-2" noMargin>{t('karaoke.editor.' + type.toLowerCase())}</Title>
        {!hasAnUpload && <span>{t('karaoke.editor.no_file')}</span>}
        {
            hasAnUpload && fileUrl && <>
                {
                    (type === 'VIDEO' || type === 'TRANSPARENT_VIDEO')
                    && <video src={fileUrl} controls className="w-full" />
                }

                {
                    (type === 'INSTRUMENTAL' || type === 'VOCALS' || type === 'FULL')
                    && <audio src={fileUrl} controls className="w-full" />
                }
            </>
        }
        {hasAnUpload && type === 'CDG' && <span>{t('karaoke.editor.cdg_uploaded')}</span>}

        <UploadMediaButton song={song} type={type} onUploadComplete={onMediaUploaded} />
    </Card>
}