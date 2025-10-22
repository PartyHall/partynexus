<?php

namespace App\Message;

use App\Entity\User;

readonly class ForgottenPasswordNotification extends AbstractPersonMessage
{
    public function __construct(
        private string $code,
        User $user,
    ) {
        parent::__construct($user);
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
