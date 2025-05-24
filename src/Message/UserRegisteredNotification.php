<?php

namespace App\Message;

use App\Entity\User;

class UserRegisteredNotification extends AbstractPersonMessage
{
    public function __construct(User $user, private readonly string $code)
    {
        parent::__construct($user);
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
