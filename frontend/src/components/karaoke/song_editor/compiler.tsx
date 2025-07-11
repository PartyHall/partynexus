import { compileSong, decompileSong } from "@/api/karaoke";
import Button from "@/components/generic/button";
import Title from "@/components/generic/title";
import type { Song } from "@/types/karaoke";
import { enqueueSnackbar } from "notistack";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    song: Song;
    doInvalidate?: () => void;
};

export default function SongEditorCompiler({ song, doInvalidate }: Props) {
    const { t } = useTranslation();
    const [compiling, setCompiling] = useState<boolean>(false);

    const doCompile = async () => {
        if (!song.id) {
            return;
        }

        setCompiling(true);

        try {
            if (song.ready) {
                await decompileSong(song.id);
            } else {
                await compileSong(song.id);
            }

            doInvalidate?.();
        } catch (e) {
            console.error(e);
            enqueueSnackbar(t('generic.error.generic'), { variant: 'error' });
        }

        setCompiling(false);
    };

    return <div className="w-full sm:w-120 flex flex-col gap-4">
        <Title level={2} className="text-center" noMargin>
            {t('karaoke.compile.title')}
        </Title>

        <p>
            {t('karaoke.compile.text1')}
        </p>
        <p>
            {t('karaoke.compile.text2')}
        </p>
        <p>
            {t('karaoke.compile.text3')}
        </p>

        <div className="flex justify-center">
            <Button onClick={doCompile} disabled={compiling}>
                {t(`karaoke.compile.request_${song?.ready ? 'decompile' : 'compile'}`)}
            </Button>
        </div>
    </div>;
}