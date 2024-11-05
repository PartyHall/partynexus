import { Button, Card, Flex, Form, Input, Typography } from "antd";
import { FormItem } from "react-hook-form-antd";

import { useAuth } from "../hooks/auth";
import { useForm } from "react-hook-form";
import useNotification from "antd/es/notification/useNotification";
import { useTranslation } from "react-i18next";

type FormProps = {
    title: string;
    artist: string;
};

export default function SongRequestForm({onRequested}: {onRequested: () => void}) {
    const { t } = useTranslation();
    const {api} = useAuth();
    const [notif, notifCtx] = useNotification();
    const { control, handleSubmit, formState, reset } = useForm<FormProps>({
        defaultValues: {
            title: '',
            artist: '',
        },
    });

    const doRequestSong = async (data: FormProps) => {
        try {
            await api.karaoke.requestMusic(data.title, data.artist);
            notif.success({
                message: t('karaoke.request.success.title'),
                description: t('karaoke.request.success.description'),
            })

            reset();
            onRequested();
        } catch (e) {
            console.error(e);
            notif.error({
                message: t('karaoke.request.error.title'),
                description: t('karaoke.request.error.description'),
            })
        }
    }

    return <Card>
        <Typography.Title style={{margin: 0}}>{t('karaoke.request.title')}</Typography.Title>
        <Form layout='vertical' onFinish={handleSubmit(doRequestSong)}>
            <FormItem
                control={control}
                name="title"
                label={t('karaoke.request.song_title')}
            >
                <Input disabled={formState.isSubmitting} />
            </FormItem>
            <FormItem
                control={control}
                name="artist"
                label={t('karaoke.request.song_artist')}
            >
                <Input disabled={formState.isSubmitting} />
            </FormItem>

            <Flex align="center" justify="center">
                <Form.Item style={{margin: 0}}>
                    <Button type="primary" htmlType="submit" disabled={formState.isSubmitting}>
                        {t('karaoke.request.create')}
                    </Button>
                </Form.Item>
            </Flex>
        </Form>

        {notifCtx}
    </Card>;
}