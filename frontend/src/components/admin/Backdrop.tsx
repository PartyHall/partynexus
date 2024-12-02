import { Button, Card, Flex, Image, Modal, Popconfirm, Tooltip, Typography } from "antd";
import { IconEdit, IconTrash } from "@tabler/icons-react";
import { Backdrop as BackdropModel } from "../../sdk/responses/backdrop";
import { EditBackdrop } from "./EditBackdrop";
import { useState } from "react";
import { useTranslation } from "react-i18next";

type Props = {
    albumId: number;
    backdrop: BackdropModel;
    onDelete: () => void;
};

export default function Backdrop({ backdrop, albumId, onDelete }: Props) {
    const [currBackdrop, setCurrBackdrop] = useState<BackdropModel>(backdrop);
    const [isEditing, setEditing] = useState<boolean>(false);
    const { t } = useTranslation();

    return <Card>
        <Flex gap={8}>
            <Image
                key={currBackdrop.id}
                src={currBackdrop.url}
                width={128}
            />

            <Typography.Title level={4} style={{ flex: 1 }}>
                {currBackdrop.title}
            </Typography.Title>

            <Flex vertical gap={8} align="center" justify="center">
                <Tooltip title={t('generic.edit')}>
                    <Button
                        icon={<IconEdit size={20} />}
                        onClick={() => setEditing(true)}
                    />
                </Tooltip>

                <Popconfirm
                    title={t('backdrops.delete_backdrop')}
                    cancelText={t('generic.cancel')}
                    okText={t('generic.modal_im_sure')}
                    onConfirm={onDelete}
                >
                    <Tooltip title={t('generic.remove', { title: currBackdrop.title })}>
                        <Button icon={<IconTrash size={20} />} />
                    </Tooltip>
                </Popconfirm>
            </Flex>
        </Flex>

        <Modal
            open={isEditing}
            footer={null}
            onCancel={() => setEditing(false)}
            onClose={() => setEditing(false)}
        >
            <EditBackdrop
                albumId={albumId}
                backdrop={backdrop}
                onUpdated={(b: BackdropModel) => {
                    setCurrBackdrop(b);
                    setEditing(false);
                }}
            />
        </Modal>
    </Card>
}