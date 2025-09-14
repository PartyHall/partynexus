import { createSong, updateSong } from "@/api/karaoke";
import { ValidationError } from "@/api/violations_error";
import Button from "@/components/generic/button";
import EnumSelect from "@/components/generic/enum_select";
import Input from "@/components/generic/input";
import Title from "@/components/generic/title";
import type { Song, UpsertSong } from "@/types/karaoke";
import { IconDeviceFloppy, IconSearch } from "@tabler/icons-react";
import { enqueueSnackbar } from "notistack";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import ExternalSongModal from "./external_song_modal";
import SongFormCover from "./song_form_cover";
import UploadButton from "@/components/generic/upload_button";

type Props = {
    song?: Song | null;
    onSuccess?: (song: Song) => void; // Used for either invalidate route or navigate to song edit page
};

export default function SongEditorForm({ song, onSuccess }: Props) {
    const { t } = useTranslation();
    const [globalErrors, setGlobalErrors] = useState<string[]>([]);

    const [externalSongService, setExternalSongService] = useState<'spotify' | 'musicBrainz' | null>(null);

    const {
        control,
        register,
        handleSubmit,
        setError,
        formState: { errors, isSubmitting },
        reset,
        setValue,
        watch,
    } = useForm<UpsertSong>({
        defaultValues: {
            title: song?.title || '',
            artist: song?.artist || '',
            format: song?.format || '',
            quality: song?.quality || '',
            musicBrainzId: song?.musicBrainzId?.length ? song.musicBrainzId : null,
            spotifyId: song?.spotifyId?.length ? song.spotifyId : null,
            hotspot: song?.hotspot || 0,
            coverFile: null,
        }
    });

    const title = watch('title');
    const artist = watch('artist');

    const onSubmit = async (data: UpsertSong) => {
        console.log("Form data:", data);
        setGlobalErrors([]);

        try {
            let savedSong: Song | null = null;

            if (song?.id) {
                data.id = song.id;
                savedSong = await updateSong(data);

                reset(savedSong);
                reset({ coverFile: null });
            } else {
                savedSong = await createSong(data);
            }

            enqueueSnackbar(t('generic.changes_saved'), { variant: 'success' });
            onSuccess?.(savedSong);
        } catch (err) {
            if (err instanceof ValidationError) {
                const globalErrors = err.applyToReactHookForm(setError);
                if (globalErrors.length > 0) {
                    setGlobalErrors(globalErrors);
                }

                return;
            }

            console.error('Error upserting song:', err);
            setGlobalErrors([t('generic.error.generic')]);
        }
    };

    return <form className="flex flex-col gap-4 w-full sm:w-80" onSubmit={handleSubmit(onSubmit)}>
        {
            song?.id
            && <Title level={2} className="blue-glow text-center" noMargin>
                {t('karaoke.editor.metadata')}
            </Title>
        }

        <SongFormCover
            control={control}
            song={song}
            disabled={isSubmitting || song?.ready}
            {...register('coverFile')}
        />

        <Input
            label={t('karaoke.song_title')}
            {...register('title', { required: true })}
            error={errors.title}
            disabled={isSubmitting || song?.ready}
        />

        <Input
            label={t('karaoke.song_artist')}
            {...register('artist', { required: true })}
            error={errors.artist}
            disabled={isSubmitting || song?.ready}
        />

        <EnumSelect
            enumName={"song_formats"}
            label={t('karaoke.filters.format')}
            {...register('format', { required: true })}
            error={errors.format}
            disabled={isSubmitting || song?.ready}
        />

        <EnumSelect
            enumName={"song_qualities"}
            label={t('karaoke.editor.quality')}
            {...register('quality', { required: true })}
            error={errors.quality}
            disabled={isSubmitting || song?.ready}
        />

        <Input
            label={t('karaoke.editor.musicbrainz_id')}
            action={<Button
                type='button'
                disabled={song?.ready}
                onClick={() => setExternalSongService('musicBrainz')}
            >
                <IconSearch size={18} />
            </Button>}
            {...register('musicBrainzId')}
            error={errors.musicBrainzId}
            disabled={isSubmitting || song?.ready}
        />

        <Input
            label={t('karaoke.editor.spotify_id')}
            action={<Button
                type='button'
                disabled={song?.ready}
                onClick={() => setExternalSongService('spotify')}
            >
                <IconSearch size={18} />
            </Button>}
            {...register('spotifyId')}
            error={errors.spotifyId}
            disabled={isSubmitting || song?.ready}
        />

        <Input
            type="number"
            label={t('karaoke.editor.hotspot')}
            {...register('hotspot')}
            error={errors.hotspot}
            disabled={isSubmitting || song?.ready}
        />

        {
            globalErrors.length > 0 && (
                <div className="text-red-glow mb-4">
                    {
                        globalErrors.map((error, index) => (
                            <p key={index}>{error}</p>
                        ))
                    }
                </div>
            )
        }

        {
            externalSongService
            && <ExternalSongModal
                service={externalSongService}
                defaultArtist={artist}
                defaultTitle={title}
                open={!!externalSongService}
                onClose={(es) => {
                    if (es) {
                        setValue('title', es.title);
                        setValue('artist', es.artist);
                        setValue(
                            externalSongService === 'spotify'
                                ? 'spotifyId'
                                : 'musicBrainzId',
                            es.id
                        );

                    }

                    setExternalSongService(null);
                }}
            />
        }

        <Button disabled={isSubmitting || song?.ready}>
            <IconDeviceFloppy size={18} />
            {t('generic.save')}
        </Button>
    </form>;
}