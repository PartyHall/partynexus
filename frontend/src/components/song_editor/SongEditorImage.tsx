import { Button, Flex, Upload } from "antd";
import { IconUpload } from "@tabler/icons-react";
import PlaceholderCover from '../../assets/placeholder_cover.webp';
import PnSong from "../../sdk/responses/song";
import { useAuth } from "../../hooks/auth";
import useNotification from "antd/es/notification/useNotification";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    song: PnSong;
    setSong: (x: PnSong|null) => void;
};

export default function SongEditorImage({ song, setSong }: Props) {
    const { t } = useTranslation();
    const [notif, notifCtx] = useNotification();
    const [coverUploading, setCoverUploading] = useState<boolean>(false);

    const {api} = useAuth();

    return <Flex className="SongEditor__Image">
        <img
            src={song.coverUrl ? song.coverUrl : PlaceholderCover}
            alt="song cover"
        />

        <Upload
            accept=".jpg,.jpeg,.webp,.png"
            showUploadList={false}
            beforeUpload={file => {
                const isValid = [
                    'image/jpeg',
                    'image/webp',
                    'image/png',
                ].includes(file.type);

                if (!isValid) {
                    notif.error({
                        message: t('karaoke.editor.cover.bad_format.title'),
                        description: t('karaoke.editor.cover.bad_format.description'),
                    })
                }

                return isValid;
            }}
            customRequest={async (x) => {
                if (!song) {
                    return;
                }

                try {
                    setCoverUploading(true);

                    const newSong = await api.karaoke.uploadFile(
                        song,
                        'cover',
                        x.file,
                    );

                    setSong(newSong);
                    setCoverUploading(false);
                } catch (e) {
                    setCoverUploading(false);

                    console.error(e);
                    notif.error({
                        message: t('karaoke.editor.cover.failed.title'),
                        description: t('karaoke.editor.cover.failed.description'),
                    });
                }
            }}>
            <Button
                type="primary"
                icon={<IconUpload size={20} />}
                shape="circle"
                disabled={song?.ready || coverUploading}
            />
        </Upload>

        {notifCtx}
    </Flex>
}