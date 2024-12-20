import { Button, Card, Flex, Typography, Upload } from 'antd';
import { IconCheck, IconX } from '@tabler/icons-react';
import PnSong from '../../sdk/responses/song';
import { useAuth } from '../../hooks/auth';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Props = {
    type: string;
    song: PnSong;
    mimetypes: string[];
    extensions: string[];
};

/**
 * @TODO: If a file is already uploaded, we should be able to listen to it
 * (Or view it if its a video)
 */
export default function SongFileUploader({ type, song, extensions }: Props) {
    const [notif, notifCtx] = useNotification();
    const { api } = useAuth();
    const { t } = useTranslation();
    const [uploading, setUploading] = useState<boolean>(false);

    return (
        <Card>
            <Flex vertical gap={8}>
                <Typography.Title level={3} style={{ margin: 0 }}>
                    {type}:{' '}
                    {type === 'lyrics' && (
                        <>
                            {song.cdgFileUploaded ? (
                                <IconCheck size={20} />
                            ) : (
                                <IconX size={20} />
                            )}
                        </>
                    )}
                </Typography.Title>

                <Flex align="center" justify="space-around">
                    {((type === 'instrumental' && !song.instrumentalUrl) ||
                        (type === 'vocals' && !song.vocalsUrl) ||
                        (type === 'full' && !song.combinedUrl)) &&
                        t('karaoke.editor.upload.no_file_uploaded')}
                    {type === 'instrumental' &&
                        song.instrumentalUrl &&
                        (song.format?.toLowerCase() === 'video' ||
                            song.format?.toLowerCase() ===
                                'transparent_video') && (
                            <video
                                style={{ width: '50%' }}
                                controls
                                src={song.instrumentalUrl}
                            />
                        )}
                    {type === 'instrumental' &&
                        song.instrumentalUrl &&
                        song.format?.toLowerCase() === 'cdg' && (
                            <audio controls src={song.instrumentalUrl} />
                        )}
                    {type === 'vocals' && song.vocalsUrl && (
                        <audio controls src={song.vocalsUrl} />
                    )}
                    {type === 'full' && song.combinedUrl && (
                        <audio controls src={song.combinedUrl} />
                    )}

                    <Upload
                        accept={extensions.join(',')}
                        showUploadList={false}
                        /*
          // This lib is crap, it doesn't detect the mimetype of cdg
          // which should be at least "application/cdg"
          beforeUpload={file => {
            console.log(file.type)
              const isValid = mimetypes.includes(file.type);

              if (!isValid) {
                  notif.error({
                      message: t('karaoke.editor.upload.bad_format.title'),
                      description: t('karaoke.editor.upload.bad_format.description'),
                  })
              }

              return isValid;
          }}
           */
                        customRequest={async (x) => {
                            try {
                                setUploading(true);

                                await api.karaoke.uploadFile(
                                    song,
                                    type,
                                    x.file
                                );

                                setUploading(false);
                            } catch (e) {
                                setUploading(false);

                                console.error(e);
                                notif.error({
                                    message: t(
                                        'karaoke.editor.upload.failed.title'
                                    ),
                                    description: t(
                                        'karaoke.editor.upload.failed.description'
                                    ),
                                });
                            }
                        }}
                    >
                        <Button disabled={song.ready || uploading}>
                            {t('karaoke.editor.upload.choose_bt')}
                        </Button>
                    </Upload>
                </Flex>

                {notifCtx}
            </Flex>
        </Card>
    );
}
