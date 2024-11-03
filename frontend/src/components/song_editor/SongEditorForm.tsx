import { Button, Flex, Form, Input, InputNumber, Select, Upload } from "antd";
import { IconDeviceFloppy, IconSearch, IconUpload } from "@tabler/icons-react";

import ExternalSongSearch from "./ExternalSongSearch";
import { FormItem } from "react-hook-form-antd";
import PlaceholderCover from '../../assets/placeholder_cover.webp';
import PnSong from "../../sdk/responses/song";
import { ValidationErrors } from "../../sdk/responses/validation_error";

import { useAuth } from "../../hooks/auth";
import { useForm } from "react-hook-form";
import { useNavigate } from "react-router-dom";
import useNotification from "antd/es/notification/useNotification";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    isCreating: boolean;
    song: PnSong | null;
    setSong: (song: PnSong | null) => void;
}

/**
 * @TODO: Display on the left the cover
 * and the form on the right
 * On small screen the cover goes under the form (Or on top? to test)
 */
export default function SongEditorForm({ isCreating, song, setSong }: Props) {
    const [notif, ctxNotif] = useNotification();
    const { t } = useTranslation();
    const { api } = useAuth();
    const navigate = useNavigate();

    const [coverUploading, setCoverUploading] = useState<boolean>(false);
    const [showMusicBrainzSearch, setShowMusicBrainzSearch] = useState<boolean>(false);
    const [showSpotifySearch, setShowSpotifySearch] = useState<boolean>(false);

    const { control, handleSubmit, setError, formState, setValue } = useForm<PnSong>({
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
                navigate(`/karaoke/${resp?.id}`)
                return;
            }

            setSong(resp);
        } catch (e) {
            if (e instanceof ValidationErrors) {
                // @ts-expect-error BECAUSE THIS FUCKING LANGUAGE SUCKS
                e.errors.forEach(x => setError(x.fieldName, { type: 'custom', 'message': x.getText() }));
                return;
            }

            console.error(e);
            notif.error({
                message: 'Unknown error occured',
                description: 'See console for more details',
            })
        }
    };
    return <Flex className="SongEditorForm" align="start" gap={16}>
        {
            <Flex className="SongEditor__Image">
                <img
                    src={song?.coverUrl ? song.coverUrl : PlaceholderCover}
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
            </Flex>
        }

        <Form
            style={{ width: 300, marginTop: 16, margin: 'auto' }}
            layout='vertical'
            onFinish={handleSubmit(doUpdateSong)}
        >
            <FormItem
                control={control}
                name="title"
                label={t('karaoke.editor.title')}
            >
                <Input disabled={formState.isSubmitting || song?.ready} required />
            </FormItem>

            <FormItem control={control} name="artist" label={t('karaoke.editor.artist')}>
                <Input disabled={formState.isSubmitting || song?.ready} required />
            </FormItem>

            <FormItem control={control} name="format" label={t('karaoke.editor.format')}>
                {/*<EnumSelect disabled={formState.isSubmitting || song?.ready} enumName="song_formats" />*/}
                <Select options={[
                    { value: 'video', label: 'Video' },
                    { value: 'cdg', label: 'MP3+CDG' },
                    { value: 'transparent_video', label: 'Transparent video' },
                ]} disabled={formState.isSubmitting || song?.ready} />
            </FormItem>

            <FormItem control={control} name="quality" label={t('karaoke.editor.quality')}>
                {/*<EnumSelect disabled={formState.isSubmitting || song?.ready} enumName="song_qualities" />*/}
                <Select options={[
                    { value: 'bad', label: 'Bad' },
                    { value: 'ok', label: 'Ok' },
                    { value: 'good', label: 'Good' },
                    { value: 'perfect', label: 'Perfect' },
                ]} disabled={formState.isSubmitting || song?.ready} />
            </FormItem>

            {/*
                @TODO: Fix, the buttons is not aligned with textbox but I can't put the input inside
                because RHF+antd sucks
            */}
            <Flex gap={8} align="center">
                <FormItem control={control} name="musicBrainzId" label="MusicBrainz ID" style={{ flex: 1 }}>
                    <Input disabled={formState.isSubmitting || song?.ready} />
                </FormItem>
                <Button
                    icon={<IconSearch size={18} />}
                    onClick={() => setShowMusicBrainzSearch(true)}
                    disabled={formState.isSubmitting || song?.ready}
                />
                <ExternalSongSearch
                    provider='MusicBrainz'
                    shown={showMusicBrainzSearch}
                    close={() => setShowMusicBrainzSearch(false)}
                    setId={(id: string) => setValue('musicBrainzId', id, { shouldDirty: true })}
                />
            </Flex>

            <Flex gap={8} align="center">
                <FormItem control={control} name="spotifyId" label="Spotify ID" style={{flex: 1}}>
                    <Input disabled={formState.isSubmitting || song?.ready} />
                </FormItem>
                <Button
                    icon={<IconSearch size={18} />}
                    onClick={() => setShowSpotifySearch(true)}
                    disabled={formState.isSubmitting || song?.ready}
                />
                <ExternalSongSearch
                    provider='Spotify'
                    shown={showSpotifySearch}
                    close={() => setShowSpotifySearch(false)}
                    setId={(id: string) => setValue('spotifyId', id, { shouldDirty: true })}
                />
            </Flex>

            <FormItem control={control} name="hotspot" label="Hotspot">
                <InputNumber disabled={formState.isSubmitting || song?.ready} min={0} style={{ width: '100%' }} />
            </FormItem>

            {
                !song?.ready
                && <Flex align="center" justify="center" style={{ marginTop: 32 }}>
                    <Form.Item>
                        <Button
                            type="primary"
                            htmlType="submit"
                            disabled={formState.isSubmitting}
                            icon={<IconDeviceFloppy size={20}/>}
                        >
                            {t('karaoke.editor.save')}
                        </Button>
                    </Form.Item>
                </Flex>
            }
        </Form>

        {ctxNotif}
    </Flex>
}