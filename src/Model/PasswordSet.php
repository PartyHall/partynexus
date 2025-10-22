<?php

namespace App\Model;

use App\Validator\CorrectPassword;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordSet
{
    public const string API_SET_PASSWORD = 'api:password:set';
    public const string API_FORGOTTEN_PASSWORD = 'api:forgotten_password:set';

    #[CorrectPassword(groups: [self::API_SET_PASSWORD])]
    public ?string $oldPassword = null;

    #[Assert\NotCompromisedPassword(message: 'validation.not_compromised', groups: [self::API_SET_PASSWORD, self::API_FORGOTTEN_PASSWORD])]
    #[Assert\NotBlank(message: 'validation.not_blank', groups: [self::API_SET_PASSWORD, self::API_FORGOTTEN_PASSWORD])]
    #[Assert\Length(min: 8, groups: [self::API_SET_PASSWORD, self::API_FORGOTTEN_PASSWORD])]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/',
        message: 'validation.password_requirements',
        groups: [self::API_SET_PASSWORD, self::API_FORGOTTEN_PASSWORD],
    )]
    public string $newPassword = '';
}
