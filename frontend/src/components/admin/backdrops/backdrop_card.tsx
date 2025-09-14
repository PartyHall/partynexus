import { deleteBackdrop } from "@/api/photobooth/backdrops";
import Button from "@/components/generic/button";
import Card from "@/components/generic/card";
import ConfirmButton from "@/components/generic/confirm_button";
import { Tooltip } from "@/components/generic/tooltip";
import type { Backdrop } from "@/types/backdrops";
import { IconEdit, IconTrash } from "@tabler/icons-react";
import { useTranslation } from "react-i18next";

type Props = {
    albumId: number;
    backdrop: Backdrop;
    onEdit?: (albumId: number, backdrop: Backdrop) => void;
    invalidate?: () => void;
};

export default function BackdropCard({ albumId, backdrop, invalidate, onEdit }: Props) {
    const { t } = useTranslation();

    return <Card className="min-h-26 flex flex-row align-center gap-4" noGlow>
        <img
            className="block object-cover rounded-md w-30 h-20"
            src={backdrop.url}
            alt={backdrop.title}
            loading="lazy"
        />
        <span className="flex-1">{backdrop.title}</span>
        <div className="flex flex-col items-center justify-around">
            {
                onEdit
                && <Tooltip content={t('generic.edit')}>
                    <Button onClick={() => onEdit(albumId, backdrop)}><IconEdit size={18} /></Button>
                </Tooltip>
            }
            <Tooltip content={t('generic.delete')}>
                <ConfirmButton
                    tTitle={"admin.backdrop_albums.backdrops.delete_title"}
                    tDescription={"admin.backdrop_albums.backdrops.delete_desc"}
                    tDescriptionArgs={{ title: backdrop.title }}
                    onConfirm={() => deleteBackdrop(albumId, backdrop.id)}
                    onSuccess={invalidate}
                >
                    <IconTrash size={18} />
                </ConfirmButton>
            </Tooltip>
        </div>
    </Card>
};
