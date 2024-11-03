import { Button, Flex, Typography, Upload } from "antd";
import PnSong from "../../sdk/responses/song";
import { useAuth } from "../../hooks/auth";
import useNotification from "antd/es/notification/useNotification";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    type: string;
    song: PnSong;
    mimetypes: string[];
    extensions: string[];
}

/**
 * @TODO: If a file is already uploaded, we should be able to listen to it
 * (Or view it if its a video)
 */
export default function SongFileUploader({ type, song, mimetypes, extensions }: Props) {
    const [notif, notifCtx] = useNotification();
    const { api } = useAuth();
    const { t } = useTranslation();
    const [uploading, setUploading] = useState<boolean>(false);

    return <Flex vertical gap={8}>
        <Typography.Text>{type}:</Typography.Text>
        <Upload
            accept={extensions.join(',')}
            showUploadList={false}
            beforeUpload={file => {
                const isValid = mimetypes.includes(file.type);

                if (!isValid) {
                    notif.error({
                        message: t('karaoke.editor.upload.bad_format.title'),
                        description: t('karaoke.editor.upload.bad_format.description'),
                    })
                }

                return isValid;
            }}
            customRequest={async (x) => {
                try {
                    setUploading(true);

                    await api.karaoke.uploadFile(
                        song,
                        type,
                        x.file,
                    );

                    setUploading(false);
                } catch (e) {
                    setUploading(false);

                    console.error(e);
                    notif.error({
                        message: t('karaoke.editor.upload.failed.title'),
                        description: t('karaoke.editor.upload.failed.description'),
                    });
                }
            }}
        >
            <Button disabled={song.ready || uploading}>{t('karaoke.editor.upload.choose_bt')}</Button>
        </Upload>

        {notifCtx}
    </Flex>
}