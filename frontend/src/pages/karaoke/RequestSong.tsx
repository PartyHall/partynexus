import { useRef, useState } from 'react';
import { Collection } from '../../sdk/responses/collection';
import { Flex } from 'antd';
import Loader from '../../components/Loader';
import { PnSongRequest } from '../../sdk/responses/song';
import RequestedSong from '../../components/RequestedSong';
import SearchablePaginatedList from '../../components/SearchablePaginatedList';
import SongRequestForm from '../../components/SongRequestForm';
import { useAsyncEffect } from 'ahooks';
import { useAuth } from '../../hooks/auth';

export default function RequestSong() {
    const { api } = useAuth();

    const [loaded, setLoaded] = useState<boolean>(false);
    const [songs, setSongs] = useState<Collection<PnSongRequest> | null>(null);

    const reloadRequest = useRef<() => Promise<void>>();

    useAsyncEffect(async () => {
        const retreivedSongs = await api.karaoke.getRequestedMusics();

        setSongs(retreivedSongs);
        setLoaded(true);
    }, []);

    const markAsRead = async (rs: PnSongRequest) => {
        await api.karaoke.markRequestAsDone(rs);
        if (reloadRequest.current) {
            await reloadRequest.current();
        }
    };

    return (
        <Loader loading={!loaded}>
            <SongRequestForm
                onRequested={async () => {
                    if (reloadRequest.current) {
                        await reloadRequest.current();
                    }
                }}
            />
            {songs && songs.total > 0 && (
                <Flex style={{ height: '100%', overflowY: 'auto' }}>
                    <SearchablePaginatedList
                        requestRefresh={reloadRequest}
                        doSearch={async (query: string, page: number) =>
                            api.karaoke.getRequestedMusics(page, query)
                        }
                        renderElement={(x: PnSongRequest) => (
                            <RequestedSong
                                key={x.id}
                                rs={x}
                                onDelete={markAsRead}
                            />
                        )}
                    />
                </Flex>
            )}
        </Loader>
    );
}
