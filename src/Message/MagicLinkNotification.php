<?php

namespace App\Message;

use App\Entity\User;

class MagicLinkNotification extends AbstractPersonMessage
{
    public function __construct(User $user, private readonly string $code) {
        parent::__construct($user);
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
