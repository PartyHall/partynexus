import { Button, Flex, Image, Modal, Typography } from "antd";

import {
    IconDownload,
    IconRotate,
    IconRotateClockwise,
    IconVideo,
    IconZoomIn,
    IconZoomOut
} from '@tabler/icons-react';
import Loader from "../Loader";
import PictureCard from "../PictureCard";
import { PnEvent } from "../../sdk/responses/event";
import PnPicture from "../../sdk/responses/picture";
import { useAsyncEffect } from "ahooks";
import { useAuth } from "../../hooks/auth";
import { useState } from "react";
import { useTranslation } from "react-i18next";


export default function EventPictureBar({ event }: { event: PnEvent }) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [timelapseShown, setTimelapseShown] = useState<boolean>(false);
    const [loadingPictures, setLoadingPictures] = useState<boolean>(true);

    const [firstThreePictures, setFirstThreePictures] = useState<PnPicture[]>([]);
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

    // Download method stolen from antd docs
    const onDownload = (url: string) => {
        const filename = Date.now() + '.jpg'; // @TODO: Send the format from server

        fetch(url)
            .then((response) => response.blob())
            .then((blob) => {
                const blobUrl = URL.createObjectURL(new Blob([blob]));
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                URL.revokeObjectURL(blobUrl);
                link.remove();
            });
    };

    // @TODO: Tooltip on iconbar in the picture displayer

    return <Flex vertical gap={8}>
        <Typography.Title className="red-glow ml1-2">{t('event.pictures.title')}</Typography.Title>
        <Loader loading={loadingPictures}>
            <Flex gap={8} align="center" justify="start" style={{ overflowX: 'scroll' }}> {/* @TODO: scroll not working */}
                {
                    pictures.length > 0 && <>
                        <Image.PreviewGroup
                            items={pictures.map(x => `/api/pictures/${x.id}/download`)}
                            preview={{
                                toolbarRender: (
                                    _,
                                    {
                                        transform: { scale },
                                        actions: { onRotateLeft, onRotateRight, onZoomOut, onZoomIn },
                                        image: { url }
                                    },
                                ) => (
                                    <Flex gap={16} className="ant-image-preview-operations" style={{ padding: '.5em' }}>
                                        <Button shape="circle" icon={<IconRotate size={18} />} onClick={onRotateLeft} />
                                        <Button shape="circle" icon={<IconRotateClockwise size={18} />} onClick={onRotateRight} />
                                        <Button shape="circle" icon={<IconZoomIn size={18} />} disabled={scale === 1} onClick={onZoomOut} />
                                        <Button shape="circle" icon={<IconZoomOut size={18} />} disabled={scale === 50} onClick={onZoomIn} />
                                        <Button shape="circle" icon={<IconDownload size={18} />} onClick={() => onDownload(url)} />
                                    </Flex>
                                ),
                            }}
                        >
                            {firstThreePictures.map(x => <PictureCard key={x.id} picture={x} />)}
                        </Image.PreviewGroup>
                        {
                            pictures.length !== firstThreePictures.length &&
                            <Typography.Link>{t('event.pictures.more')}</Typography.Link>
                            // @TODO: The link should be clickable
                        }
                    </>
                }

                {
                    pictures.length === 0 && <Typography.Text>{t('event.pictures.no_pictures')}</Typography.Text>
                }
            </Flex>
        </Loader>
        <Flex align="center" justify="center">
            <Button icon={<IconVideo />} onClick={() => setTimelapseShown(true)}>{t('event.show_timelapse_bt')}</Button>
        </Flex>

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
}