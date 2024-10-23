import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";

import Loader from "../../components/Loader";
import PnSong from "../../sdk/responses/song";
import SongEditor from "../../components/song_editor/SongEditor";
import { Typography } from "antd";

import { useAsyncEffect } from "ahooks";
import { useAuth } from "../../hooks/auth";

export default function EditSongPage() {
    const { id } = useParams();
    const { api, isGranted } = useAuth();
    const navigate = useNavigate();

    const [error, setError] = useState<string | null>(null);
    const [fetchingSong, setFetchingSong] = useState<boolean>(true);
    const [song, setSong] = useState<PnSong | null>(null);


    useAsyncEffect(async () => {
        try {
            const s = await api.karaoke.getSong(parseInt(id || '0'));
            setSong(s);
            setFetchingSong(false);
        } catch (e) {
            setError('Failed to fetch song, see console for more info');
            console.error(e);
        }
    }, [id]);

    
    useEffect(() => {
        if (!isGranted('ROLE_ADMIN')) {
            navigate('/');
        }
    }, [api]);

    return <>
        { error && <Typography.Title>{error}</Typography.Title> }
        {
            !error &&
            <Loader loading={fetchingSong}>
                <SongEditor song={song} />
            </Loader>
        }
    </>
}