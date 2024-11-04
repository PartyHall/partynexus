<?php

namespace App\Message;

readonly class UserRegisteredNotification
{
    public function __construct(
        private string $language,
        private string $username,
        private string $userEmail,
    ) {
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
}
