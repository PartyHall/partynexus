<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\Processor\OAuthLoginProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/login_oauth',
            processor: OAuthLoginProcessor::class,
        )
    ],
)]
class LoginOAuth
{
    #[Assert\NotBlank]
    public string $code;
}
