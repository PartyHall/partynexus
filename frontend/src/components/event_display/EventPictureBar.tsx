import { Flex, Image, Typography } from "antd";
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

    return <>
        <Typography.Title>{t('event.pictures.title')}</Typography.Title>
        <Loader loading={loadingPictures}>
            <Flex gap={8} align="center" justify="start">
                <Image.PreviewGroup items={pictures.map(x => x.iri + '/download')}>
                    {firstThreePictures.map(x => <PictureCard key={x.iri} picture={x} />)}
                </Image.PreviewGroup>
                {
                    pictures.length !== firstThreePictures.length &&
                    <>More...</>
                }
            </Flex>
        </Loader>
    </>
}