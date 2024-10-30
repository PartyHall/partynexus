import { Button, Flex, Form, Input, InputNumber, Select } from "antd";

import { FormItem } from "react-hook-form-antd";
import PnSong from "../../sdk/responses/song";
import { Search } from "lucide-react";
import { ValidationErrors } from "../../sdk/responses/validation_error";

import { useAuth } from "../../hooks/auth";
import { useForm } from "react-hook-form";
import { useNavigate } from "react-router-dom";
import useNotification from "antd/es/notification/useNotification";
import { useTranslation } from "react-i18next";

type Props = {
    isCreating: boolean;
    song: PnSong | null;
    setSong: (song: PnSong|null) => void;
}

/**
 * @TODO: Display on the left the cover
 * and the form on the right
 * On small screen the cover goes under the form (Or on top? to test)
 */
export default function SongEditorForm({isCreating, song, setSong}: Props) {
    const [notif, ctxNotif] = useNotification();
    const {t} = useTranslation();
    const { api } = useAuth();
    const navigate = useNavigate();

    const { control, handleSubmit, setError, formState } = useForm<PnSong>({
        defaultValues: {
            title: song?.title,
            artist: song?.artist,
            format: song?.format,
            quality: song?.quality,
            musicbrainzId: song?.musicbrainzId,
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
                e.errors.forEach(x => setError(x.fieldName, {type: 'custom', 'message': x.getText()}));
                return;
            }

            console.error(e);
            notif.error({
                message: 'Unknown error occured',
                description: 'See console for more details',
            })
        }
    };
    return <>
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
                <Input disabled={formState.isSubmitting} required />
            </FormItem>

            <FormItem control={control} name="artist" label={t('karaoke.editor.artist')}>
                <Input disabled={formState.isSubmitting} required />
            </FormItem>

            <FormItem control={control} name="format" label={t('karaoke.editor.format')}>
                {/*<EnumSelect disabled={formState.isSubmitting} enumName="song_formats" />*/}
                <Select options={[
                    { value: 'video', label: 'Video' },
                    { value: 'cdg', label: 'MP3+CDG' },
                    { value: 'transparent_video', label: 'Transparent video' },
                ]} />
            </FormItem>

            <FormItem control={control} name="quality" label={t('karaoke.editor.quality')}>
                {/*<EnumSelect disabled={formState.isSubmitting} enumName="song_qualities" />*/}
                <Select options={[
                    { value: 'bad', label: 'Bad' },
                    { value: 'ok', label: 'Ok' },
                    { value: 'good', label: 'Good' },
                    { value: 'perfect', label: 'Perfect' },
                ]} />
            </FormItem>

            <FormItem control={control} name="musicbrainzId" label="MusicBrainz ID">
                <Flex gap={8}>
                    <Input disabled={formState.isSubmitting} />
                    <Button icon={<Search size={18} />} /> {/* this button should show a modal with result from the MusicBrainz API (proxied through backend) */}
                </Flex>
            </FormItem>

            <FormItem control={control} name="spotifyId" label="Spotify ID">
                <Flex gap={8}>
                    <Input disabled={formState.isSubmitting} />
                    <Button icon={<Search size={18} />} /> {/* this button should show a modal with result from the Spotify API (proxied through backend) */}
                </Flex>
            </FormItem>

            <FormItem control={control} name="hotspot" label="Hotspot">
                <InputNumber disabled={formState.isSubmitting} min={0} style={{ width: '100%' }} />
            </FormItem>

            <Flex align="center" justify="center" style={{ marginTop: 32 }}>
                <Form.Item>
                    <Button type="primary" htmlType="submit" disabled={formState.isSubmitting}>
                        {t('karaoke.editor.save')}
                    </Button>
                </Form.Item>
            </Flex>
        </Form>

        {ctxNotif}
    </>
}