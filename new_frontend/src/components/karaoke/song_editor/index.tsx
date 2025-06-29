import type { Song } from "@/types/karaoke";
import Title from "../../generic/title";
import { useTranslation } from "react-i18next";
import SongEditorForm from "./form";
import SongEditorCompiler from "./compiler";

type Props = {
    song?: Song | null;
    onSuccess?: (song: Song) => void; // Used for either invalidate route or navigate to song edit page
};

export default function SongEditor({ song, onSuccess }: Props) {
    const { t } = useTranslation();
    return <div className="pageContainer">
        <Title noMargin>{t('karaoke.editor.title_' + (song ? 'edit' : 'new'), { name: song?.title })}</Title>

        {
            song
            && song.id
            && <SongEditorCompiler song={song} />
        }

        <SongEditorForm song={song} onSuccess={onSuccess} />
    </div>
}