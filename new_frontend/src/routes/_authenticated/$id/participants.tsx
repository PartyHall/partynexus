import Title from '@/components/generic/title'
import { createFileRoute } from '@tanstack/react-router'
import { useTranslation } from 'react-i18next'
import Participant from '@/components/event/participant';
import { useEventStore } from '@/stores/event';

import AsyncSelect from 'react-select/async';
import { getUsers } from '@/api/users';
import { useState } from 'react';
import Modal from '@/components/generic/modal';
import Button from '@/components/generic/button';
import { enqueueSnackbar } from 'notistack';
import { addParticipantToEvent } from '@/api/events/participants';
import { useAuthStore } from '@/stores/auth';

export const Route = createFileRoute('/_authenticated/$id/participants')({
  component: RouteComponent,
})

/**
 * Note: wanted to use @luciodale/react-searchable-dropdown as
 * the author kindly added support for async searching but its
 * still too early and doesn't work at all here, maybe we'll 
 * revisit it later, let's use react-select for now.
 * 
 * @see https://github.com/luciodale/react-searchable-dropdown/issues/3
 */

function RouteComponent() {
  const { t } = useTranslation()
  const { isGranted, tokenUser } = useAuthStore();
  const { event, setEvent } = useEventStore();

  const [userToAdd, setUserToAdd] = useState<any>(null);
  const [isAdding, setIsAdding] = useState(false);

  const addParticipant = async () => {
    if (!userToAdd || !event) {
      return;
    }

    try {
      setIsAdding(true);
      const updatedEvent = await addParticipantToEvent(event, userToAdd.value);
      setIsAdding(false);

      setEvent(updatedEvent);
      enqueueSnackbar(
        t('events.editor.participants.add_success', { username: userToAdd.label }),
        { variant: 'success' },
      );
    } catch (e) {
      console.error('Error adding participant:', e);
      enqueueSnackbar(t('generic.error.generic'), { variant: 'error' });
    }

    setUserToAdd(null);
  };

  if (!event) {
    throw new Error('Event loading');
  }

  return <div className='flex flex-col gap-4'>
    <Title noMargin className='text-center'>{t('events.participants')}</Title>

    {
      (isGranted('ROLE_ADMIN') || tokenUser?.id === event.owner.id)
      && <div className='flex flex-row items-center gap-2 mb-4'>
        <span>{t('events.editor.participants.add')}: </span>

        <AsyncSelect
          value={null}
          placeholder={t('generic.search')}
          options={[{ value: 'test', label: 'toto' }, { value: 'toto', label: 'test' }]}
          menuPosition='fixed'
          unstyled
          loadOptions={async (val: string) => (await getUsers({ search: val })).member.map(u => ({ value: u['@id'], label: u.username }))}
          defaultOptions
          onChange={setUserToAdd}
          classNames={{
            container: () => 'flex-1',
            control: () => 'bg-synthbg-600 text-primary-200 rounded-sm px-3',
            menu: () => 'bg-synthbg-600 text-primary-200 mt-1 rounded-sm',
            option: ({ isSelected, isFocused }) => `cursor-pointer rounded-sm p-1 ${isSelected || isFocused ? 'bg-synthbg-400' : ''}`,
            singleValue: () => 'text-primary-200',
          }}
        />
      </div>
    }

    <ul>
      <Participant key={event.owner.id} event={event} participant={event.owner} owner />

      {
        event.participants.map((participant) => <Participant
          key={participant.id}
          event={event}
          participant={participant}
          canRemove={isGranted('ROLE_ADMIN') || tokenUser?.id === event.owner.id}
        />)
      }
    </ul>

    <Modal
      open={!!userToAdd}
      onOpenChange={() => setUserToAdd(null)}
      title={t('events.editor.participants.add')}
      description={t('events.editor.participants.add_disclaimer', { username: userToAdd?.label })}
    >
      <div className='flex flex-row gap-2 align-center justify-end w-full'>
        <Button onClick={() => setUserToAdd(null)} variant="secondary">{t('generic.cancel')}</Button>
        <Button onClick={addParticipant} disabled={isAdding}>{t('generic.yes')}</Button>
      </div>
    </Modal>
  </div>
}
