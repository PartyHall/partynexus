<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordSet
{
    public ?string $oldPassword = null;

    #[Assert\NotCompromisedPassword(message: 'validation.not_compromised')]
    #[Assert\NotBlank(message: 'validation.not_blank')]
    #[Assert\Length(min: 8)]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/',
        message: 'validation.password_requirements',
    )]
    public string $newPassword = '';
}
