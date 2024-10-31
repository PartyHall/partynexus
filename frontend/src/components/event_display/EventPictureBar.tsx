import { Button, Flex, Image, Space, Typography } from "antd";
import Loader from "../Loader";
import PictureCard from "../PictureCard";
import { PnEvent } from "../../sdk/responses/event";
import PnPicture from "../../sdk/responses/picture";
import { useAsyncEffect } from "ahooks";
import { useAuth } from "../../hooks/auth";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import {
    DownloadOutlined,
    LeftOutlined,
    RightOutlined,
    RotateLeftOutlined,
    RotateRightOutlined,
    SwapOutlined,
    UndoOutlined,
    ZoomInOutlined,
    ZoomOutOutlined,
} from '@ant-design/icons';

export default function EventPictureBar({ event }: { event: PnEvent }) {
    const { t } = useTranslation();
    const { api } = useAuth();
    const [loadingPictures, setLoadingPictures] = useState<boolean>(true);

    const [firstThreePictures, setFirstThreePictures] = useState<PnPicture[]>([]);
    const [pictures, setPictures] = useState<PnPicture[]>([]);

    useAsyncEffect(async () => {
        setLoadingPictures(true);

        const pictures = await api.events.getPictures(event.id, true);
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

    return <>
        <Typography.Title>{t('event.pictures.title')}</Typography.Title>
        <Loader loading={loadingPictures}>
            <Flex gap={8} align="center" justify="start">
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
                                        image: {url}
                                    },
                                ) => (
                                    <Flex gap={16} className="ant-image-preview-operations" style={{padding: '.5em'}}>
                                        <Button shape="circle" icon={<RotateLeftOutlined/>} onClick={onRotateLeft} />
                                        <Button shape="circle" icon={<RotateRightOutlined/>} onClick={onRotateRight} />
                                        <Button shape="circle" icon={<ZoomOutOutlined/>} onClick={onRotateRight} disabled={scale === 1} onClick={onZoomOut} />
                                        <Button shape="circle" icon={<ZoomInOutlined/>} onClick={onRotateRight} disabled={scale === 50} onClick={onZoomIn} />
                                        <Button shape="circle" icon={<DownloadOutlined/>} onClick={() => onDownload(url)} />
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
    </>
}