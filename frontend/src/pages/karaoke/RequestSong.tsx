import { Collection } from "../../sdk/responses/collection";
import { Flex } from "antd";
import Loader from "../../components/Loader";
import { PnSongRequest } from "../../sdk/responses/song";
import RequestedSong from "../../components/RequestedSong";
import SearchablePaginatedList from "../../components/SearchablePaginatedList";
import SongRequestForm from "../../components/SongRequestForm";
import { useAsyncEffect } from "ahooks";
import { useAuth } from "../../hooks/auth";
import { useState } from "react";

/*
     @TODO: Use a context for the SearchableList so that
     we can ask it to refresh from outside
*/

export default function RequestSong() {
    const {api} = useAuth();

    const [loaded, setLoaded] = useState<boolean>(false);
    const [songs, setSongs] = useState<Collection<PnSongRequest>|null>(null);

    useAsyncEffect(async () => {
        const retreivedSongs = await api.karaoke.getRequestedMusics();

        setSongs(retreivedSongs);
        setLoaded(true);
    }, []);

    const markAsRead = async (rs: PnSongRequest) => {
        await api.karaoke.markRequestAsDone(rs);
    };

    return <Loader loading={!loaded}>
        <SongRequestForm />
        {
            songs && songs.total > 0 &&
            <Flex style={{ height: '100%', overflowY: 'auto' }}>
                <SearchablePaginatedList
                    doSearch={async (query: string, page: number) => api.karaoke.getRequestedMusics(
                        page,
                        query,
                    )}
                    renderElement={(x: PnSongRequest) => <RequestedSong 
                        key={x.id}
                        rs={x}
                        onDelete={markAsRead}
                    />}
                />
            </Flex>
        }
    </Loader>;
}