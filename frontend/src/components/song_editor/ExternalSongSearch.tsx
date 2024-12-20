import { Card, Flex, Input, Modal, Typography } from 'antd';
import { useAsyncEffect, useDebounce } from 'ahooks';
import { useEffect, useState } from 'react';

import { Collection } from '../../sdk/responses/collection';
import { PnExternalSong } from '../../sdk/responses/song';

import { useAuth } from '../../hooks/auth';
import { useTranslation } from 'react-i18next';

type Props = {
    provider: string;
    shown: boolean;
    close: () => void;
    setId: (id: string) => void;

    getTitle: () => string;
    getArtist: () => string;
};

function DisplayExternalSong({
    song,
    onClick,
}: {
    song: PnExternalSong;
    onClick: () => void;
}) {
    const { t } = useTranslation();

    return (
        <Card onClick={onClick} className="song">
            <Flex gap={8} align="center">
                {song.cover && <img src={song.cover} />}
                <Flex vertical flex="1">
                    <Typography.Text>
                        {t('karaoke.editor.search_external.track')}:{' '}
                        {song.title}
                    </Typography.Text>
                    <Typography.Text>
                        {t('karaoke.editor.search_external.artist')}:{' '}
                        {song.artist}
                    </Typography.Text>
                    <Typography.Text>
                        {t('karaoke.editor.search_external.id')}: {song.id}
                    </Typography.Text>
                </Flex>
            </Flex>
        </Card>
    );
}

export default function ExternalSongSearch({
    shown,
    close,
    setId,
    provider,
    getTitle,
    getArtist,
}: Props) {
    const { t } = useTranslation();
    const { api } = useAuth();

    const [songs, setSongs] = useState<Collection<PnExternalSong> | null>(null);

    const [artist, setArtist] = useState<string>('');
    const [track, setTrack] = useState<string>('');

    const artistDebounced = useDebounce(artist, { wait: 500 });
    const trackDebounced = useDebounce(track, { wait: 500 });

    useEffect(() => {
        if (!shown) {
            return;
        }

        setArtist(getArtist());
        setTrack(getTitle());
    }, [shown]);

    useAsyncEffect(async () => {
        if (
            !shown ||
            artistDebounced.length === 0 ||
            trackDebounced.length === 0
        ) {
            return;
        }

        const data = await api.karaoke.searchExternal(
            provider,
            artistDebounced,
            trackDebounced
        );

        setSongs(data);
    }, [shown, artistDebounced, trackDebounced]);

    return (
        <Modal
            open={shown}
            onCancel={close}
            onClose={close}
            title={t('karaoke.editor.search_external.title', { provider })}
            footer={[]}
        >
            <Flex vertical gap={8}>
                <Flex vertical>
                    <Typography.Text>
                        {t('karaoke.editor.search_external.artist')}:
                    </Typography.Text>
                    <Input
                        value={artist}
                        onChange={(x) => setArtist(x.target.value)}
                    />
                    <Typography.Text>
                        {t('karaoke.editor.search_external.track')}:
                    </Typography.Text>
                    <Input
                        value={track}
                        onChange={(x) => setTrack(x.target.value)}
                    />
                </Flex>

                <Flex vertical gap={8} className="externalSongSearchList">
                    {songs &&
                        songs.items.map((x) => (
                            <DisplayExternalSong
                                key={x.id}
                                song={x}
                                onClick={() => {
                                    setId(x.id);
                                    close();
                                }}
                            />
                        ))}
                </Flex>
            </Flex>
        </Modal>
    );
}
