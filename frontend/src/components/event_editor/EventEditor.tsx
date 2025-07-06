import { Button, DatePicker, Flex, Form, Input, Typography } from 'antd';
import DisplayBoardCreator from './DisplayBoardCreator';
import { FormItem } from 'react-hook-form-antd';
import { PnEvent } from '../../sdk/responses/event';
import { ValidationErrors } from '../../sdk/responses/validation_error';
import { useAuth } from '../../hooks/auth';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';
import useNotification from 'antd/es/notification/useNotification';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

type Props = {
    event: PnEvent | null;
};

export default function EventEditor({ event: initialEvent }: Props) {
    const [notif, ctxNotif] = useNotification();
    const [event, setEvent] = useState<PnEvent | null>(initialEvent);
    const { t } = useTranslation();
    const { api } = useAuth();
    const navigate = useNavigate();
    const isCreating = !initialEvent;

    const { control, handleSubmit, setError, formState } = useForm<PnEvent>({
        defaultValues: {
            id: event?.id,
            name: event?.name,
            author: event?.author,
            datetime: event?.datetime,
            location: event?.location,
            userRegistrationEnabled: event?.userRegistrationEnabled ?? false,
        },
    });

    const doUpdateEvent = async (data: PnEvent) => {
        try {
            const resp = await api.events.upsert(data);

            if (isCreating) {
                navigate(`/events/${resp?.id}/edit`);
                return;
            }

            setEvent(resp);
        } catch (e) {
            if (e instanceof ValidationErrors) {
                e.errors.forEach((x) =>
                    setError(x.fieldName as keyof PnEvent, {
                        type: 'custom',
                        message: x.getText(),
                    })
                );
                return;
            }

            console.error(e);
            notif.error({
                message: 'Unknown error occured',
                description: 'See console for more details',
            });
        }
    };

    return (
        <Flex vertical gap={16}>
            <Typography.Title className="blue-glow">
                {t(
                    isCreating
                        ? 'event.editor.create_title'
                        : 'event.editor.edit_title',
                    { title: event?.name }
                )}
            </Typography.Title>

            <Form
                style={{ width: 300, marginTop: 16, margin: 'auto' }}
                layout="vertical"
                onFinish={handleSubmit(doUpdateEvent)}
            >
                <FormItem control={control} name="name" label={t('event.name')}>
                    <Input disabled={formState.isSubmitting} />
                </FormItem>

                <FormItem
                    control={control}
                    name="author"
                    label={t('event.author')}
                >
                    <Input disabled={formState.isSubmitting} />
                </FormItem>

                <FormItem
                    control={control}
                    name="datetime"
                    label={t('event.datetime')}
                >
                    <DatePicker
                        disabled={formState.isSubmitting}
                        required
                        style={{ width: '100%' }}
                    />
                </FormItem>

                <FormItem
                    control={control}
                    name="location"
                    label={t('event.location')}
                >
                    <Input disabled={formState.isSubmitting} />
                </FormItem>

                <FormItem
                    control={control}
                    name="userRegistrationEnabled"
                    label={t('event.user_registration_enabled')}
                    valuePropName="checked"
                >
                    <Input
                        type="checkbox"
                        disabled={formState.isSubmitting}
                    />
                </FormItem>

                {
                    event
                    && <DisplayBoardCreator event={event} />
                }

                <Flex align="center" justify="center" style={{ marginTop: 32 }}>
                    <Form.Item>
                        <Button
                            type="primary"
                            htmlType="submit"
                            disabled={formState.isSubmitting}
                        >
                            {t('generic.save')}
                        </Button>
                    </Form.Item>
                </Flex>
            </Form>

            {ctxNotif}
        </Flex>
    );
}
