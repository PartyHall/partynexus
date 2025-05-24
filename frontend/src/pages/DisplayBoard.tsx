import { Flex } from "antd";
import styles from '../assets/css/displayboard.module.scss';
import { useAsyncEffect } from "ahooks";
import { useParams } from "react-router-dom";
import { useState } from "react";

type DisplayBoardQueryParams = {
    eventId: string;
    displayBoardKey: string;
};

type Picture = {
    id: string;
    url: string;
};

export default function DisplayBoardPage() {
    const { eventId, displayBoardKey } = useParams<DisplayBoardQueryParams>();

    const [pictures, setPictures] = useState<Picture[]>([]);
    const [error, setError] = useState<string | null>();

    const fetchPictures = async () => {
        try {
            const resp = await fetch(`/api/events/${eventId}/pictures?displayBoardKey=${displayBoardKey}`,
                {
                    // We want the DISPLAY BOARD KEY to be used and absolutely not
                    // the cookies
                    // as the amount of picture changes depending on the authenticated user
                    // and cookies are used first
                    credentials: 'omit', 
                }
            );
            if (!resp.ok) {
                throw new Error(`Failed to fetch pictures: ${resp.statusText}`);
            }

            const data = await resp.json();
            setPictures(data['member'].map((x: any) => ({
                id: x.id,
                url: `/api/pictures/${x.id}/download?displayBoardKey=${displayBoardKey}`,
            })));
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An unknown error occurred');
        }
    };

    const loopFetchPictures = async () => {
        setTimeout(async () => {
            await fetchPictures();
            loopFetchPictures();
        }, 5000);
    };

    useAsyncEffect(async () => {
        await fetchPictures();
        await loopFetchPictures();
    }, [eventId, displayBoardKey]);

    if (error) {
        return <Flex vertical justify="center" align="center" style={{ width: '100vw', height: '100vh', color: 'red' }}>
            <h1>Error</h1>
            <p>{error}</p>
        </Flex>
    }

    // Fuck antd i need to move from this crap QUICKLY
    return <div className={styles.displayBoard}>
        {
            pictures.map(x => <img key={x.id} src={x.url} className={styles.image} />)
        }
    </div>
}