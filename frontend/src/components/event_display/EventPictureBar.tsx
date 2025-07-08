import 'yet-another-react-lightbox/styles.css';
import "yet-another-react-lightbox/plugins/counter.css";

import { Button, Flex, Modal, Typography } from 'antd';
import { Counter, Download, Fullscreen, Zoom } from 'yet-another-react-lightbox/plugins';
import { IconVideo } from '@tabler/icons-react';

import Lightbox from 'yet-another-react-lightbox';

import Loader from '../Loader';
import { PnEvent } from '../../sdk/responses/event';
import PnPicture from '../../sdk/responses/picture';
import { useAsyncEffect } from 'ahooks';
import { useAuth } from '../../hooks/auth';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function EventPictureBar({ event }: { event: PnEvent }) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [timelapseShown, setTimelapseShown] = useState<boolean>(false);
    const [loadingPictures, setLoadingPictures] = useState<boolean>(true);

    const [carrousel, setCarrousel] = useState<boolean>(false);

    const [firstThreePictures, setFirstThreePictures] = useState<PnPicture[]>(
        []
    );
    const [pictures, setPictures] = useState<PnPicture[]>([]);

    useAsyncEffect(async () => {
        setLoadingPictures(true);

        const pictures = await api.events.getPictures(event.id, false);
        if (!pictures) {
            // @TODO: Display error
            setLoadingPictures(false);
            return;
        }

        const firstThree = [];
        const all = [];

        for (let i = 0; i < pictures.items.length; i++) {
            if (i < 3) {
                firstThree.push(pictures.items[i]);
            }
            all.push(pictures.items[i]);
        }

        setFirstThreePictures(firstThree);
        setPictures(all);

        setLoadingPictures(false);
    }, []);

    // @TODO: Tooltip on iconbar in the picture displayer

    return (
        <Flex vertical gap={8}>
            <Typography.Title className="red-glow ml1-2">
                {t('event.pictures.title')}
            </Typography.Title>
            <Loader loading={loadingPictures}>
                <Flex
                    gap={8}
                    align="center"
                    justify="start"
                    style={{ overflowX: 'auto', maxHeight: '100px' }}
                >
                    <Lightbox
                        open={carrousel}
                        close={() => setCarrousel(false)}
                        plugins={[Counter, Fullscreen, Download, Zoom]}
                        zoom={{
                            maxZoomPixelRatio: 20,
                        }}
                        slides={pictures.map((x) => ({
                            src: `/api/pictures/${x.id}/download`,
                            alt: x.id,
                        }))}
                    />
                    {
                        firstThreePictures.map(x => <img
                            key={x.id}
                            src={`/api/pictures/${x.id}/download`}
                            onClick={() => setCarrousel(true)}
                            style={{ display: 'block', height: '100%' }}
                        />)
                    }

                    {
                        pictures.length !== 0 && <Button onClick={() => setCarrousel(true)}>Voir plus</Button>
                    }

                    {pictures.length === 0 && (
                        <Typography.Title level={3}>
                            {t('event.pictures.no_pictures')}
                        </Typography.Title>
                    )}
                </Flex>
            </Loader>
            {event.export && event.export.timelapse && (
                <Flex align="center" justify="center">
                    <Button
                        icon={<IconVideo />}
                        onClick={() => setTimelapseShown(true)}
                    >
                        {t('event.show_timelapse_bt')}
                    </Button>
                </Flex>
            )}

            <Modal
                title={t('event.pictures.timelapse')}
                open={timelapseShown}
                footer={[]}
                onClose={() => setTimelapseShown(false)}
                onCancel={() => setTimelapseShown(false)}
            >
                <video
                    className="timelapse_video"
                    src={`/api/events/${event.id}/timelapse`}
                    controls
                />
            </Modal>
        </Flex>
    );
}
