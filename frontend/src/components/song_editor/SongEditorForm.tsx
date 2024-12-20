import { Button, Flex, Form, Input, InputNumber, Select } from 'antd';
import { IconDeviceFloppy, IconSearch } from '@tabler/icons-react';

import ExternalSongSearch from './ExternalSongSearch';
import { FormItem } from 'react-hook-form-antd';
import PnSong from '../../sdk/responses/song';
import SongEditorImage from './SongEditorImage';
import { ValidationErrors } from '../../sdk/responses/validation_error';

import { useAuth } from '../../hooks/auth';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Props = {
    isCreating: boolean;
    song: PnSong | null;
    setSong: (song: PnSong | null) => void;
};

export default function SongEditorForm({ isCreating, song, setSong }: Props) {
    const [notif, ctxNotif] = useNotification();
    const { t } = useTranslation();
    const { api } = useAuth();
    const navigate = useNavigate();

    const [showMusicBrainzSearch, setShowMusicBrainzSearch] =
        useState<boolean>(false);
    const [showSpotifySearch, setShowSpotifySearch] = useState<boolean>(false);

    const { control, handleSubmit, setError, formState, setValue, getValues } =
        useForm<PnSong>({
            defaultValues: {
                id: song?.id,
                title: song?.title,
                artist: song?.artist,
                format: song?.format,
                quality: song?.quality,
                musicBrainzId: song?.musicBrainzId,
                spotifyId: song?.spotifyId,
                hotspot: song?.hotspot,
            },
        });

    const doUpdateSong = async (data: PnSong) => {
        try {
            const resp = await api.karaoke.upsertSong(data);

            if (isCreating) {
                navigate(`/karaoke/${resp?.id}`);
                return;
            }

            setSong(resp);
        } catch (e) {
            if (e instanceof ValidationErrors) {
                // @ts-expect-error BECAUSE THIS FUCKING LANGUAGE SUCKS
                e.errors.forEach((x) =>
                    setError(x.fieldName, {
                        type: 'custom',
                        message: x.getText(),
                    })
                );
                return;
            }

            console.error(e);
            notif.error({
                message: 'Unknown error occured',
                description: 'See console for more details',
            });
        }
    };
    return (
        <Flex className="SongEditorForm" align="start" gap={16}>
            {!isCreating && song && (
                <SongEditorImage song={song} setSong={setSong} />
            )}

            <Form
                style={{ width: 300, marginTop: 16, margin: 'auto' }}
                layout="vertical"
                onFinish={handleSubmit(doUpdateSong)}
            >
                <FormItem
                    control={control}
                    name="title"
                    label={t('karaoke.editor.title')}
                >
                    <Input
                        disabled={formState.isSubmitting || song?.ready}
                        required
                    />
                </FormItem>

                <FormItem
                    control={control}
                    name="artist"
                    label={t('karaoke.editor.artist')}
                >
                    <Input
                        disabled={formState.isSubmitting || song?.ready}
                        required
                    />
                </FormItem>

                <FormItem
                    control={control}
                    name="format"
                    label={t('karaoke.editor.format')}
                >
                    {/*<EnumSelect disabled={formState.isSubmitting || song?.ready} enumName="song_formats" />*/}
                    <Select
                        options={[
                            { value: 'video', label: 'Video' },
                            { value: 'cdg', label: 'MP3+CDG' },
                            {
                                value: 'transparent_video',
                                label: 'Transparent video',
                            },
                        ]}
                        disabled={formState.isSubmitting || song?.ready}
                    />
                </FormItem>

                <FormItem
                    control={control}
                    name="quality"
                    label={t('karaoke.editor.quality')}
                >
                    {/*<EnumSelect disabled={formState.isSubmitting || song?.ready} enumName="song_qualities" />*/}
                    <Select
                        options={[
                            { value: 'bad', label: 'Bad' },
                            { value: 'ok', label: 'Ok' },
                            { value: 'good', label: 'Good' },
                            { value: 'perfect', label: 'Perfect' },
                        ]}
                        disabled={formState.isSubmitting || song?.ready}
                    />
                </FormItem>

                {/*
                @TODO: Fix, the buttons is not aligned with textbox but I can't put the input inside
                because RHF+antd sucks
            */}
                <Flex gap={8} align="center" justify="end">
                    <FormItem
                        control={control}
                        name="musicBrainzId"
                        label="MusicBrainz ID"
                        style={{ flex: 1 }}
                    >
                        <Input
                            disabled={formState.isSubmitting || song?.ready}
                        />
                    </FormItem>
                    <Button
                        icon={<IconSearch size={18} />}
                        onClick={() => setShowMusicBrainzSearch(true)}
                        disabled={formState.isSubmitting || song?.ready}
                    />
                    <ExternalSongSearch
                        provider="MusicBrainz"
                        shown={showMusicBrainzSearch}
                        close={() => setShowMusicBrainzSearch(false)}
                        setId={(id: string) =>
                            setValue('musicBrainzId', id, { shouldDirty: true })
                        }
                        getTitle={() => getValues().title}
                        getArtist={() => getValues().artist}
                    />
                </Flex>

                <Flex gap={8} align="center">
                    <FormItem
                        control={control}
                        name="spotifyId"
                        label="Spotify ID"
                        style={{ flex: 1 }}
                    >
                        <Input
                            disabled={formState.isSubmitting || song?.ready}
                        />
                    </FormItem>
                    <Button
                        icon={<IconSearch size={18} />}
                        onClick={() => setShowSpotifySearch(true)}
                        disabled={formState.isSubmitting || song?.ready}
                    />
                    <ExternalSongSearch
                        provider="Spotify"
                        shown={showSpotifySearch}
                        close={() => setShowSpotifySearch(false)}
                        setId={(id: string) =>
                            setValue('spotifyId', id, { shouldDirty: true })
                        }
                        getTitle={() => getValues().title}
                        getArtist={() => getValues().artist}
                    />
                </Flex>

                <FormItem control={control} name="hotspot" label="Hotspot">
                    <InputNumber
                        disabled={formState.isSubmitting || song?.ready}
                        min={0}
                        style={{ width: '100%' }}
                    />
                </FormItem>

                {!song?.ready && (
                    <Flex
                        align="center"
                        justify="center"
                        style={{ marginTop: 32 }}
                    >
                        <Form.Item>
                            <Button
                                type="primary"
                                htmlType="submit"
                                disabled={formState.isSubmitting}
                                icon={<IconDeviceFloppy size={20} />}
                            >
                                {t('generic.save')}
                            </Button>
                        </Form.Item>
                    </Flex>
                )}
            </Form>

            {ctxNotif}
        </Flex>
    );
}
