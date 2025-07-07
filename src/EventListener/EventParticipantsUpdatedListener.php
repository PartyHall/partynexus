<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;

/**
 * When the participant list was updated on an event.
 */
#[AsDoctrineListener(Events::onFlush)]
class EventParticipantsUpdatedListener
{
    public function onFlush(OnFlushEventArgs $args): void
    {
        $manager = $args->getObjectManager();
        $uow = $manager->getUnitOfWork();

        foreach ($uow->getScheduledCollectionUpdates() as $update) {
            if (!$update->getOwner() instanceof Event || !$update instanceof PersistentCollection) {
                continue;
            }

            $event = $update->getOwner();

            $currentParticipants = $manager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->innerJoin('u.participatingEvents', 'e')
                ->where('e.id = :eventId')
                ->setParameter('eventId', $event->getId())
                ->getQuery()
                ->getResult();

            /** @var User[] $updateElts */
            $updateElts = $update->toArray();

            $currentParticipantIds = \array_map(fn (User $user) => $user->getId(), $currentParticipants);
            $newParticipantIds = \array_map(fn (User $user) => $user->getId(), $updateElts);

            $addedParticipantIds = \array_diff($newParticipantIds, $currentParticipantIds);
            $removedParticipantIds = \array_diff($currentParticipantIds, $newParticipantIds);

            $addedParticipants = \array_filter($updateElts, fn (User $user) => \in_array($user->getId(), $addedParticipantIds));
            $removedParticipants = \array_filter($currentParticipants, fn (User $user) => \in_array($user->getId(), $removedParticipantIds));

            // @TODO: Send mail when added / removed
        }
    }
}
