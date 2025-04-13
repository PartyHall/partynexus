<?php

namespace App\Message;

use App\Entity\User;

abstract class AbstractPersonMessage
{
    private string $username;
    private string $fullname;
    private string $userEmail;
    private string $language;

    public function __construct(User $user)
    {
        $this->username = $user->getUsername();
        $this->fullname = $user->getFullName();
        $this->userEmail = $user->getEmail();
        $this->language = $user->getLanguage();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
