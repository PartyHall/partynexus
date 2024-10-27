import { Button, Flex, Typography } from "antd";
import PnSong from "../../sdk/responses/song";
import SongEditorForm from "./SongEditorForm";
import SongFileUploader from "./SongFileUploader";
import Title from "antd/es/typography/Title";

import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    song: PnSong | null;
};

export default function SongEditor({ song: initialSong }: Props) {
    const [song, setSong] = useState<PnSong | null>(initialSong);
    const { t } = useTranslation();
    const isCreating = !initialSong;

    return <Flex vertical gap={16}>
        <Typography.Title className="blue-glow">
            {t(isCreating ? 'karaoke.editor.title_new' : 'karaoke.editor.title_edit', { title: song?.title })}
        </Typography.Title>

        <SongEditorForm isCreating={isCreating} song={song} setSong={setSong} />

        {
            !isCreating && song &&
            <>
                <Flex vertical gap={8}>
                    <Title className="blue-glow" style={{ margin: 0 }}>{t('karaoke.editor.song_files')}</Title>

                    <SongFileUploader type="instrumental" song={song} />
                    {
                        song?.format === 'cdg' &&
                        <SongFileUploader type="lyrics" song={song} />
                    }
                    <SongFileUploader type="vocals" song={song} />
                    <SongFileUploader type="full" song={song} />
                </Flex>

                <Flex vertical>
                    <Title className="blue-glow">{t('karaoke.editor.compile.title')}</Title>
                    <Typography.Paragraph>{t('karaoke.editor.compile.text1')}</Typography.Paragraph>
                    <Typography.Paragraph>{t('karaoke.editor.compile.text2')}</Typography.Paragraph>
                    <Typography.Paragraph>{t('karaoke.editor.compile.text3')}</Typography.Paragraph>

                    <Flex align="center" justify="center">
                        <Button>{t('karaoke.editor.compile.request_' + (song?.ready ? 'decompile' : 'compile'))}</Button>
                    </Flex>
                </Flex>
            </>
        }

    </Flex>
}