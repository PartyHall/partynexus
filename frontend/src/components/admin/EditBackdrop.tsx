import { Button, Flex, Form, Input, Upload, UploadFile, UploadProps } from "antd";
import { useEffect, useMemo, useState } from "react";
import { Backdrop as BackdropModel } from "../../sdk/responses/backdrop";
import { FormItem } from "react-hook-form-antd";
import { IconDeviceFloppy } from "@tabler/icons-react";
import { UploadOutlined } from "@ant-design/icons";
import { useAuth } from "../../hooks/auth";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

type EditBackdropProps = {
    albumId: number;
    backdrop: BackdropModel;
    onUpdated: (b: BackdropModel) => void;
};

export function EditBackdrop({ backdrop, albumId, onUpdated }: EditBackdropProps) {
    const { t } = useTranslation();
    const { api } = useAuth();

    const [fileList, setFileList] = useState<UploadFile[]>([]);

    const { control, handleSubmit, formState, reset } = useForm<BackdropModel>({
        defaultValues: useMemo(() => ({
            id: backdrop.id,
            title: backdrop.title,
            file: undefined,
        }), [backdrop]),
    });

    useEffect(() => reset({ ...backdrop }), [backdrop]);

    const submit = async (data: BackdropModel) => {
        data.file = fileList.length > 0 ? fileList[0] : undefined;

        let newBackdrop = null;
        if (data.id) {
            newBackdrop = await api.backdrops.updateBackdrop(albumId, data);
        } else {
            newBackdrop = await api.backdrops.createBackdrop(albumId, data);
        }

        if (!newBackdrop) {
            return;
        }
        onUpdated(newBackdrop);
    }

    const uploadProps: UploadProps = {
        beforeUpload: (file) => {
            const isValidFormat = file.type === 'image/png' || file.type === 'image/webp';
            if (!isValidFormat) {
                return Upload.LIST_IGNORE;
            }

            // Prevents auto-upload
            return false;
        },
        maxCount: 1,
        fileList,
        onChange: ({ fileList: newFileList }) => {
            setFileList(newFileList);
        },
    };

    return <Form onFinish={handleSubmit(submit)}>
        <Flex vertical style={{ padding: '0 1.5em' }}>
            <FormItem
                control={control}
                name="title"
                label={t('backdrops.edit_backdrop.backdrop_title')}
            >
                <Input disabled={formState.isSubmitting} required />
            </FormItem>
        </Flex>

        {
            backdrop.id === 0
            && <Flex vertical style={{ padding: '0 1.5em' }}>
                <FormItem
                    control={control}
                    name="file"
                    label={t('backdrops.edit_backdrop.backdrop_file')}
                >
                    <Upload
                        {...uploadProps}
                        accept=".png,.webp"
                    >
                        <Button icon={<UploadOutlined size={18} />} disabled={formState.isSubmitting}>
                            {t('generic.choose')}
                        </Button>
                    </Upload>
                </FormItem>
            </Flex>
        }

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
}