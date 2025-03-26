<?php

namespace App\Message;

readonly class TestEmailNotification
{
    public function __construct(
        private string $email,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
