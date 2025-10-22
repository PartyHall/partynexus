<?php

namespace App\Security;

use App\Entity\Appliance;
use App\Entity\Event;
use App\Interface\HasEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, HasEvent|Event>
 */
class EventVoter extends Voter
{
    public const string PARTICIPANT = 'EVENT_PARTICIPANT';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::PARTICIPANT === $attribute;
    }

    /**
     * @param HasEvent $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$subject) {
            return true;
        }

        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        $isAppliance = false;
        if ($user instanceof Appliance) {
            $user = $user->getOwner();
            $isAppliance = true;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $event = $subject instanceof Event ? $subject : $subject->getEvent();

        if ($event->getOwner() === $user) {
            return true;
        }

        // When we are on an appliance
        // It must be the appliance of the owner of the event
        if (!$isAppliance) {
            // DO NOT TRANSFORM THIS INTO ARRAY_ANY AS PHPSTORM WANTS TO
            // SINCE ITS A DOCTRINE COLLECTION AND NOT AN ARRAY
            foreach ($event->getParticipants() as $participant) {
                if ($participant === $user) {
                    return true;
                }
            }
        }

        return false;
    }
}
