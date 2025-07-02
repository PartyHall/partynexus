import Title from '@/components/generic/title'
import { KeyVal } from '@/components/generic/key_val';
import dayjs from 'dayjs';
import type { Export } from '@/types/export';
import { useTranslation } from 'react-i18next';
import ConfirmButton from '@/components/generic/confirm_button';
import concludeEvent from '@/api/events/conclude';
import { enqueueSnackbar } from 'notistack';

type Props = {
    eventId: string;
    exp: Export|null;
}

export default function SettingsExport({ eventId, exp }: Props) {
    const { t } = useTranslation();

    const doConcludeEvent = async () => {
        try {
            await concludeEvent(eventId);
        } catch (err) {
            console.error(err);
            enqueueSnackbar(t('generic.error.generic'), { variant: 'error' });
        }
    };

    if (!exp) {
        return <div className='flex flex-col gap-0.5'>
            <Title level={2} className='mb-2' noMargin>{t('events.settings.export.title')}</Title>
            <p className='text-center'>{t('events.settings.concluded_no_export')}</p>
        </div>;
    }

    return <div className='flex flex-col gap-0.5'>
        <Title level={2} className='mb-2' noMargin>{t('events.settings.export.title')}</Title>

        <KeyVal label='events.settings.export.started_at'>
            {dayjs(exp.startedAt).format('L - LT')}
        </KeyVal>

        {
            exp.status.value !== 'started' && exp.endedAt
            && <>
                <KeyVal label='events.settings.export.ended_at'>
                    {dayjs(exp.endedAt).format('L - LT')}
                </KeyVal>
            </>
        }

        {
            exp.status.value === 'started'
            && <KeyVal label='events.settings.export.progress'>{exp.progress.label}</KeyVal>
        }

        <KeyVal label='events.settings.export.status'>{exp.status.label}</KeyVal>

        {
            exp.status.value !== 'started'
            && <ConfirmButton
                className='w-full mt-4'
                tTitle={'events.settings.export.force_reexport'}
                tDescription={'events.settings.export.force_reexport_desc'}
                onConfirm={doConcludeEvent}>
                {t('events.settings.export.force_reexport')}
            </ConfirmButton>
        }
    </div>
}