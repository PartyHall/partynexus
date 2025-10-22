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
class EventOwnerVoter extends Voter
{
    public const string OWNER = 'EVENT_OWNER';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::OWNER === $attribute;
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
        if (!$user || $user instanceof Appliance) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $event = $subject instanceof Event ? $subject : $subject->getEvent();

        if ($event->getOwner() === $user) {
            return true;
        }

        return false;
    }
}
