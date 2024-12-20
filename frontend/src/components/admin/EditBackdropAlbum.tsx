import { Button, Flex, Form, Input, InputNumber } from 'antd';
import { useEffect, useMemo, useState } from 'react';
import { BackdropAlbum } from '../../sdk/responses/backdrop';
import { FormItem } from 'react-hook-form-antd';
import { IconDeviceFloppy } from '@tabler/icons-react';
import Loader from '../Loader';
import { useAsyncEffect } from 'ahooks';
import { useAuth } from '../../hooks/auth';
import { useForm } from 'react-hook-form';
import { useTranslation } from 'react-i18next';

type Props = {
    albumId?: number;
    onUpserted: () => void;
};

type EditorProps = {
    album: BackdropAlbum | null;
    onUpserted: () => void;
};

const Editor = ({ album, onUpserted }: EditorProps) => {
    const { t } = useTranslation();
    const { api } = useAuth();
    const { control, handleSubmit, formState, reset } = useForm<BackdropAlbum>({
        defaultValues: useMemo(
            () => ({
                id: album?.id,
                title: album?.title ?? '',
                author: album?.author ?? '',
                version: album?.version ?? 1,
            }),
            [album]
        ),
    });

    const submit = async (data: BackdropAlbum) => {
        await api.backdrops.upsertAlbum(data);
        onUpserted();
    };

    useEffect(() => reset({ ...album }), [album]);

    return (
        <Form onFinish={handleSubmit(submit)}>
            <Flex vertical style={{ padding: '0 1.5em' }}>
                <FormItem
                    control={control}
                    name="title"
                    label={t('backdrops.edit_album.album_title')}
                >
                    <Input disabled={formState.isSubmitting} />
                </FormItem>

                <FormItem
                    control={control}
                    name="author"
                    label={t('backdrops.edit_album.album_author')}
                >
                    <Input disabled={formState.isSubmitting} />
                </FormItem>

                <FormItem
                    control={control}
                    name="version"
                    label={t('backdrops.edit_album.album_version')}
                >
                    <InputNumber
                        disabled={formState.isSubmitting}
                        min={1}
                        style={{ width: '100%' }}
                    />
                </FormItem>
            </Flex>

            <Flex align="center" justify="center">
                <Form.Item style={{ marginBottom: 0 }}>
                    <Button
                        type="primary"
                        htmlType="submit"
                        disabled={formState.isSubmitting}
                        icon={<IconDeviceFloppy size={20} />}
                    >
                        {t('generic.save')}
                    </Button>
                </Form.Item>
            </Flex>
        </Form>
    );
};

export default function EditBackdropAlbum({ albumId, onUpserted }: Props) {
    const { api } = useAuth();

    const [loadingAlbum, setLoadingAlbum] = useState<boolean>(
        albumId !== undefined
    );
    const [album, setAlbum] = useState<BackdropAlbum | null>();

    useAsyncEffect(async () => {
        if (!albumId) {
            return;
        }

        if (albumId > 0) {
            setAlbum(await api.backdrops.getAlbum(albumId));
        } else {
            setAlbum(new BackdropAlbum());
        }

        setLoadingAlbum(false);
    }, [albumId]);

    return (
        <Loader loading={loadingAlbum}>
            {album && <Editor album={album ?? null} onUpserted={onUpserted} />}
        </Loader>
    );
}
