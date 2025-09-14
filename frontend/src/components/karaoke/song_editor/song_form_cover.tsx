import { Tooltip } from "@/components/generic/tooltip";
import UploadButton from "@/components/generic/upload_button";
import type { Song } from "@/types/karaoke";
import { IconUpload } from "@tabler/icons-react";
import { useEffect, useState } from "react";
import { useController, type Control, type FieldValues, type Path } from "react-hook-form";
import { useTranslation } from "react-i18next";

const placeholderCoverUrl = 'https://placehold.co/300x300/171520/d72793/png';

type Props<T extends FieldValues> = {
    song?: Song | null;
    name: Path<T>;
    disabled?: boolean;
    control: Control<T>;
};

export default function SongFormCover<T extends FieldValues>({ song, name, disabled, control }: Props<T>) {
    const { t } = useTranslation();
    const [selectedFileName, setSelectedFileName] = useState<string | null>(null);
    const [songUrl, setSongUrl] = useState<string>(song?.coverUrl || placeholderCoverUrl);

    const file = useController({name, control}).field.value as File | null;

    useEffect(() => {
        if (!file) {
            setSelectedFileName(null);
            setSongUrl(song?.coverUrl || placeholderCoverUrl);
        }
    }, [file]);

    return (
        <div className="w-full h-auto max-w-[150px] m-auto relative aspect-square">
            <img
                src={songUrl}
                alt={'Song cover'}
                className="rounded-lg w-full h-full block object-cover"
            />
            <div className="absolute bottom-2 right-2">
                <Tooltip content={t('karaoke.editor.upload_cover')}>
                    <UploadButton
                        accept=".png, .jpg, .jpeg"
                        disabled={disabled}
                        name={name}
                        control={control}
                        rules={{ }}
                        hideError
                        hideFilename
                        onChange={file => {
                            setSelectedFileName(file ? file.name : null)
                            setSongUrl(file ? URL.createObjectURL(file) : placeholderCoverUrl);
                        }}
                    >
                        <IconUpload size={18} />
                    </UploadButton>
                </Tooltip>
            </div>
            {selectedFileName && (
                <div className="absolute w-full mt-2 text-xs text-primary text-center text-primary-glow animate-pulse top-0 flex flex-col bg-primary-900/90">
                    <span className="font-bold">{selectedFileName}</span>
                    <span className="ml-2 opacity-80">{t('karaoke.editor.not_uploaded_yet')}</span>
                </div>
            )}
        </div>
    );
}