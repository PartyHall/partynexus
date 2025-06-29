import { Tooltip } from "@/components/generic/tooltip";
import UploadButton from "@/components/generic/upload_button";
import type { Song } from "@/types/karaoke";
import { IconUpload } from "@tabler/icons-react";
import { useState, type ForwardedRef } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    song?: Song|null;
    name: string;
    disabled?: boolean;
    onBlur?: React.FocusEventHandler<HTMLInputElement>;
    inputRef?: ForwardedRef<HTMLInputElement>;
    onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
};

export default function SongFormCover({ song, name, disabled, onBlur, inputRef, onChange }: Props) {
    const { t } = useTranslation();
    const [selectedFileName, setSelectedFileName] = useState<string | null>(null);

    return (
        <div className="w-full h-auto max-w-[150px] m-auto relative">
            <img
                src={song?.coverUrl ?? 'https://placehold.co/300x300/171520/d72793/png'}
                alt={'Song cover'}
                className="rounded-lg"
            />
            <div className="absolute bottom-2 right-2">
                <Tooltip content={t('karaoke.editor.upload_cover')}>
                    <UploadButton
                        accept=".png, .jpg, .jpeg"
                        name={name}
                        ref={inputRef}
                        onBlur={onBlur}
                        disabled={disabled}
                        onChange={e => {
                            const file = e.target.files?.[0] || null;
                            setSelectedFileName(file ? file.name : null);
                            onChange?.(e);
                        }}
                    >
                        <IconUpload size={18} />
                    </UploadButton>
                </Tooltip>
            </div>
            {selectedFileName && (
                <div className="mt-2 text-xs text-primary text-center text-primary-glow animate-pulse">
                    <span className="font-bold">{selectedFileName}</span>
                    <span className="ml-2 opacity-80">{t('karaoke.editor.not_uploaded_yet', 'Not uploaded yet')}</span>
                </div>
            )}
        </div>
    );
}