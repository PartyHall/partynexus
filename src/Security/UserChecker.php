<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    )
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getBannedAt() !== null) {
            throw new AccountExpiredException($this->translator->trans('users.login.banned', locale: $user->getLanguage()));
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
