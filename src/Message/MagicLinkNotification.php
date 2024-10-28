<?php

namespace App\Message;

readonly class MagicLinkNotification
{
    public function __construct(
        private string $language,
        private string $username,
        private string $userEmail,
        private string $code,
    )
    {
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

    public function getCode(): string
    {
        return $this->code;
    }
}
