import Username from '@/components/username';
import type { MinimalUser } from '@/types/user';
import { IconCrown, IconUserX } from '@tabler/icons-react';
import Modal from '@/components/generic/modal';
import { useState } from 'react';
import Button from '@/components/generic/button';
import { Tooltip } from '@/components/generic/tooltip';
import { Trans, useTranslation } from 'react-i18next';
import type { Event } from '@/types/event';
import { removeParticipantFromEvent } from '@/api/events/participants';
import { enqueueSnackbar } from 'notistack';
import { useRouter } from '@tanstack/react-router';

type Props = {
    participant: MinimalUser;
    owner?: boolean;
    event: Event;
};

export default function Participant({ event, participant, owner }: Props) {
    const { t } = useTranslation();
    const {invalidate} = useRouter();

    const [modalOpen, setModalOpen] = useState(false);

    const removeParticipant = async () => {
        try {
            await removeParticipantFromEvent(event, participant['@id']);
            await invalidate();
            enqueueSnackbar(t('events.editor.participants.remove_success'), { variant: 'success' });
            setModalOpen(false);
        } catch (e) {
            console.error(e);
            enqueueSnackbar(t('generic.error.generic'), { variant: 'error' });
        }
    };

    return <li className='flex flex-row items-center justify-between p-1 border-b last:border-b-0 border-synthbg-500'>
        <div className='flex flex-col gap-0'>
            <Username user={participant} noStyle />
            <span className='pl-5 text-sm text-primary-200'>{participant.username}</span>
        </div>
        {
            owner
            && <IconCrown className='icon-blue-glow' size={18} />
        }

        {
            !owner
            && <Tooltip content={t('events.editor.participants.remove')}>
                <Button onClick={() => setModalOpen(true)}>
                    <IconUserX size={18} />
                </Button>
            </Tooltip>
        }

        <Modal title={t('events.editor.participants.remove')} open={modalOpen} onOpenChange={setModalOpen}>
            <span>
                <Trans i18nKey="events.editor.participants.remove_disclaimer" components={[<Username user={participant} />]} />
            </span>

            <div className='flex flex-row justify-end gap-2 mt-4'>
                <Button variant="secondary" onClick={() => setModalOpen(false)}>{t('generic.cancel')}</Button>
                <Button variant="danger" onClick={removeParticipant}>{t('generic.ok')}</Button>
            </div>
        </Modal>
    </li>;
}