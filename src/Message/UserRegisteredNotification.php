<?php

namespace App\Message;

use App\Entity\User;

readonly class UserRegisteredNotification extends AbstractPersonMessage
{
    public function __construct(User $user, private string $code)
    {
        parent::__construct($user);
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
