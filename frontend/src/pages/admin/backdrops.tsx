import {
    BackdropAlbum,
    Backdrop as BackdropModel,
} from '../../sdk/responses/backdrop';
import { Button, Flex, Modal, Popconfirm, Select, Typography } from 'antd';
import { IconSquareRoundedPlus, IconTrash } from '@tabler/icons-react';
import { useAsyncEffect, useTitle } from 'ahooks';
import Backdrop from '../../components/admin/Backdrop';
import { Collection } from '../../sdk/responses/collection';
import { EditBackdrop } from '../../components/admin/EditBackdrop';
import EditBackdropAlbum from '../../components/admin/EditBackdropAlbum';
import Loader from '../../components/Loader';
import { useAuth } from '../../hooks/auth';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function AdminBackdropsPage() {
    const { t } = useTranslation();
    const { api } = useAuth();

    const [selectedAlbum, setSelectedAlbum] = useState<number | null>(null);

    const [fetchingAlbums, setFetchingAlbums] = useState<boolean>(false);
    const [albums, setAlbums] = useState<Collection<BackdropAlbum> | null>(
        null
    );

    const [albumId, setAlbumId] = useState<number | null>(null);

    const [fetchingAlbum, setFetchingAlbum] = useState<boolean>(false);
    const [album, setAlbum] = useState<Collection<BackdropModel> | null>(null);

    const [editedAlbum, setEditedAlbum] = useState<number | null>(null);

    const [isAddingBackdrop, setIsAddingBackdrop] = useState<boolean>(false);

    useTitle(t('menu.admin.backdrop_management') + ' - PartyHall');

    const reloadAlbums = async () => {
        setFetchingAlbums(true);

        try {
            setAlbums(await api.backdrops.getAlbums());
        } catch (e: any) {
            console.error(e);
        }

        setFetchingAlbums(false);
    };

    useAsyncEffect(async () => {
        await reloadAlbums();
    }, []);

    const fetchAlbum = async (albumId: number | null) => {
        setSelectedAlbum(albumId);

        setFetchingAlbum(true);
        setAlbumId(albumId);

        if (!albumId) {
            setAlbum(null);
            setFetchingAlbum(false);

            return;
        }

        setAlbum(await api.backdrops.getBackdrops(albumId));
        setFetchingAlbum(false);
    };

    const deleteAlbum = async () => {
        if (!albumId) {
            return;
        }

        try {
            await api.backdrops.deleteAlbum(albumId);
            setSelectedAlbum(null);
            await reloadAlbums();
            await fetchAlbum(null);
        } catch (e: any) {
            console.error(e);
        }
    };

    const deleteBackdrop = async (
        albumId: number | null,
        backdrop: BackdropModel
    ) => {
        if (!albumId) {
            return;
        }

        try {
            await api.backdrops.deleteBackdrop(albumId, backdrop.id);
            await reloadAlbums();
            await fetchAlbum(albumId);
        } catch (e: any) {
            console.error(e);
        }
    };

    return (
        <Flex vertical gap={8}>
            <Typography.Title className="blue-glow">
                {t('backdrops.title')}
            </Typography.Title>
            <Flex gap={8}>
                <Select
                    options={[
                        { value: null, label: t('generic.no_selection') },
                        ...(albums?.items.map((x) => ({
                            value: x.id,
                            label: t('backdrops.title_and_author', {
                                title: x.title,
                                author: x.author,
                            }),
                        })) || []),
                    ]}
                    onChange={(x) => fetchAlbum(x)}
                    disabled={fetchingAlbums}
                    style={{ flex: 1 }}
                    value={selectedAlbum}
                />

                <Button
                    icon={<IconSquareRoundedPlus size={20} />}
                    onClick={() => setEditedAlbum(-1)}
                >
                    {t('backdrops.create')}
                </Button>
            </Flex>

            {!!album && (
                <Flex align="center" justify="center" gap={8}>
                    <Button
                        icon={<IconSquareRoundedPlus size={20} />}
                        onClick={() => setIsAddingBackdrop(true)}
                    >
                        {t('backdrops.add_backdrop.title')}
                    </Button>
                    <Button
                        icon={<IconSquareRoundedPlus size={20} />}
                        onClick={() => setEditedAlbum(albumId)}
                    >
                        {t('backdrops.edit_album.title')}
                    </Button>
                    <Popconfirm
                        title={t('backdrops.delete_album.description')}
                        cancelText={t('generic.cancel')}
                        okText={t('generic.modal_im_sure')}
                        onConfirm={deleteAlbum}
                    >
                        <Button icon={<IconTrash size={20} />}>
                            {t('backdrops.delete_album.title')}
                        </Button>
                    </Popconfirm>
                </Flex>
            )}

            <Loader loading={fetchingAlbum}>
                <Flex vertical gap={8}>
                    {album?.items.length === 0 && (
                        <Typography.Title
                            level={3}
                            style={{ textAlign: 'center' }}
                        >
                            {t('generic.no_results')}
                        </Typography.Title>
                    )}
                    {albumId &&
                        album?.items.map((x) => (
                            <Backdrop
                                key={x.id}
                                albumId={albumId}
                                backdrop={x}
                                onDelete={() => deleteBackdrop(albumId, x)}
                            />
                        ))}
                </Flex>
            </Loader>

            <Modal
                open={!!editedAlbum}
                footer={null}
                onCancel={() => setEditedAlbum(null)}
                onClose={() => setEditedAlbum(null)}
            >
                {editedAlbum && (
                    <EditBackdropAlbum
                        albumId={editedAlbum}
                        onUpserted={async () => {
                            await reloadAlbums();
                            setEditedAlbum(null);
                        }}
                    />
                )}
            </Modal>

            <Modal
                open={isAddingBackdrop}
                footer={null}
                onCancel={() => setIsAddingBackdrop(false)}
                onClose={() => setIsAddingBackdrop(false)}
            >
                {selectedAlbum && (
                    <EditBackdrop
                        albumId={selectedAlbum}
                        backdrop={new BackdropModel()}
                        onUpdated={() => {
                            fetchAlbum(selectedAlbum);
                            setIsAddingBackdrop(false);
                        }}
                    />
                )}
            </Modal>
        </Flex>
    );
}
