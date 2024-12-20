import { Button, Flex, Typography } from 'antd';
import PnSong from '../../sdk/responses/song';
import SongEditorForm from './SongEditorForm';
import SongFileUploader from './SongFileUploader';
import Title from 'antd/es/typography/Title';

import { useAuth } from '../../hooks/auth';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Props = {
    song: PnSong | null;
};

export default function SongEditor({ song: initialSong }: Props) {
    const [song, setSong] = useState<PnSong | null>(initialSong);
    const { t } = useTranslation();
    const { api } = useAuth();
    const [notif, notifCtx] = useNotification();
    const isCreating = !initialSong;

    const [compileInProgress, setCompileInProgress] = useState<boolean>(false);

    const doCompile = async () => {
        setCompileInProgress(true);

        let newSong: PnSong | null = null;

        try {
            if (song?.ready) {
                newSong = await api.karaoke.decompile(song);
            } else if (song) {
                newSong = await api.karaoke.compile(song);
            }

            setSong(newSong);
            notif.info({
                message: t(
                    `karaoke.editor.compile.success_${song?.ready ? 'decompile' : 'compile'}.title`
                ),
                description: t(
                    `karaoke.editor.compile.success_${song?.ready ? 'decompile' : 'compile'}.description`
                ),
            });
        } catch (e) {
            console.error(e);
            notif.error({
                message: t(
                    `karaoke.editor.compile.failure_${song?.ready ? 'decompile' : 'compile'}.title`
                ),
                description: t(
                    `karaoke.editor.compile.failure_${song?.ready ? 'decompile' : 'compile'}.description`
                ),
            });
        }

        setCompileInProgress(false);
    };

    const songFormat = song?.format?.toLowerCase();

    return (
        <Flex vertical gap={16}>
            <Typography.Title className="blue-glow">
                {t(
                    isCreating
                        ? 'karaoke.editor.title_new'
                        : 'karaoke.editor.title_edit',
                    { title: song?.title }
                )}
            </Typography.Title>

            <SongEditorForm
                isCreating={isCreating}
                song={song}
                setSong={setSong}
            />

            {!isCreating && song && (
                <>
                    {!song.ready && (
                        <>
                            <Flex vertical gap={8}>
                                <Title
                                    className="blue-glow"
                                    style={{ margin: 0 }}
                                >
                                    {t('karaoke.editor.song_files')}
                                </Title>

                                <SongFileUploader
                                    type="instrumental"
                                    song={song}
                                    mimetypes={
                                        songFormat === 'video' ||
                                        songFormat === 'transparent_video'
                                            ? ['video/webm']
                                            : ['audio/mpeg']
                                    }
                                    extensions={
                                        songFormat === 'video' ||
                                        songFormat === 'transparent_video'
                                            ? ['.webm']
                                            : ['.mp3']
                                    }
                                />
                                {song?.format === 'cdg' && (
                                    <SongFileUploader
                                        type="lyrics"
                                        song={song}
                                        mimetypes={['application/cdg']}
                                        extensions={['.cdg']}
                                    />
                                )}
                                <SongFileUploader
                                    type="vocals"
                                    song={song}
                                    mimetypes={['audio/mpeg']}
                                    extensions={['.mp3']}
                                />

                                <SongFileUploader
                                    type="full"
                                    song={song}
                                    mimetypes={['audio/mpeg']}
                                    extensions={['.mp3']}
                                />
                            </Flex>
                        </>
                    )}

                    <Flex vertical>
                        <Title className="blue-glow">
                            {t('karaoke.editor.compile.title')}
                        </Title>
                        <Typography.Paragraph>
                            {t('karaoke.editor.compile.text1')}
                        </Typography.Paragraph>
                        <Typography.Paragraph>
                            {t('karaoke.editor.compile.text2')}
                        </Typography.Paragraph>
                        <Typography.Paragraph>
                            {t('karaoke.editor.compile.text3')}
                        </Typography.Paragraph>

                        <Flex align="center" justify="center">
                            <Button
                                disabled={compileInProgress}
                                onClick={() => doCompile()}
                            >
                                {t(
                                    'karaoke.editor.compile.request_' +
                                        (song?.ready ? 'decompile' : 'compile')
                                )}
                            </Button>
                        </Flex>
                    </Flex>
                </>
            )}

            {notifCtx}
        </Flex>
    );
}
