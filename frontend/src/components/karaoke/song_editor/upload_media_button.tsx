import Button from "@/components/generic/button";
import { ProgressBar } from "@/components/generic/progress_bar";
import type { Song } from "@/types/karaoke";
import { useAuthStore } from "@/stores/auth";
import { IconUpload } from "@tabler/icons-react";
import { useState, useRef } from "react";
import { enqueueSnackbar } from "notistack";
import { useTranslation } from "react-i18next";

type Props = {
    song: Song;
    type: 'VIDEO' | 'TRANSPARENT_VIDEO' | 'INSTRUMENTAL' | 'VOCALS' | 'FULL' | 'CDG';
    onUploadComplete?: () => void;
}

export default function UploadMediaButton({ song, type, onUploadComplete }: Props) {
    const {t} = useTranslation();
    const { token } = useAuthStore();
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [isUploading, setIsUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);

    let uploadType: string = type;
    let acceptedFiletype = 'audio/mp3';
        
    if (type === 'VIDEO' || type === 'TRANSPARENT_VIDEO') {
        acceptedFiletype = 'video/webm';
        uploadType = 'instrumental'
    } else if (type === 'CDG') {
        acceptedFiletype = '.cdg';
        uploadType = 'lyrics'
    }

    const handleFileSelect = async (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (!file || !song.id || !token) {
            return;
        }

        const resetInput = (isUploading: boolean = false) => {
            setIsUploading(isUploading);
            setUploadProgress(0);

            if (!isUploading && fileInputRef.current) {
                fileInputRef.current.value = '';
            }
        };

        resetInput(true);

        try {
            const formData = new FormData();
            formData.append('file', file);

            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    setUploadProgress((event.loaded / event.total) * 100);
                }
            });

            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    enqueueSnackbar(t('generic.upload_success'), { variant: 'success' });
                    onUploadComplete?.();
                } else {
                    enqueueSnackbar(t('generic.error.upload_failed'), { variant: 'error' });
                }

                resetInput();
            };

            xhr.onerror = () => {
                enqueueSnackbar(t('generic.error.upload_failed'), { variant: 'error' });
                resetInput();
            };

            xhr.open('POST', `/api/songs/${song.id}/upload-file/${uploadType.toLowerCase()}`);
            xhr.setRequestHeader('Authorization', `Bearer ${token}`);

            xhr.send(formData);

        } catch (error) {
            console.error('Upload error:', error);
            enqueueSnackbar(t('generic.error.upload_failed'), { variant: 'error' });
            resetInput();
        }
    };

    return (
        <div className="flex flex-row gap-4 w-full items-center">
            <input
                ref={fileInputRef}
                type="file"
                onChange={handleFileSelect}
                accept={acceptedFiletype}
                className="hidden"
                disabled={isUploading}
            />

            <span>{uploadProgress.toFixed(0)}%</span>

            <ProgressBar value={isUploading ? uploadProgress : 0} />

            <Button
                onClick={() => fileInputRef.current?.click()}
                disabled={isUploading}
            >
                <IconUpload size={18} />
                {t('generic.upload')}
            </Button>
        </div>
    );
}