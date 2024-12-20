import SongEditor from '../../components/song_editor/SongEditor';
import { useTitle } from 'ahooks';
import { useTranslation } from 'react-i18next';

export default function NewSongPage() {
    const { t } = useTranslation();
    useTitle(t('karaoke.editor.title_new') + ' - PartyHall');

    return <SongEditor song={null} />;
}
