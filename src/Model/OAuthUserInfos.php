<?php

namespace App\Model;

class OAuthUserInfos
{
    public ?string $oauthUserId = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $email = null;
    public ?string $locale = null;
    public ?string $username = null;
    /** @var array<string> */
    public array $roles = [];
}
