<?php

namespace App\Message;

readonly class PasswordUpdatedNotification
{
    public function __construct(
        private string $language,
        private string $userEmail,
        private string $firstname,
        private string $lastname,
    ) {
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }
}
