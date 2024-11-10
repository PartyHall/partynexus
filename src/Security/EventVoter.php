<?php

namespace App\Security;

use App\Interface\HasEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, HasEvent>
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
        // @phpstan-ignore-next-line
        if (!$subject) {
            return true;
        }

        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($subject->getEvent()->getOwner() === $user) {
            return true;
        }

        foreach ($subject->getEvent()->getParticipants() as $participant) {
            if ($participant === $user) {
                return true;
            }
        }

        return false;
    }
}
