<?php

namespace App\Message;

readonly class UserRegisteredNotification
{
    public function __construct(
        private string $language,
        private string $username,
        private string $firstname,
        private string $lastname,
        private string $userEmail,
        private string $code,
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

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
