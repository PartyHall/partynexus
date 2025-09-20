<?php

namespace App\Enum;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

trait EnumApiResourcetrait
{
    #[Groups(EnumApiConfig::GET)]
    #[ApiProperty(identifier: true)]
    public function getId(): int|string
    {
        return $this->value;
    }

    #[Groups(EnumApiConfig::GET)]
    public function getName(): string
    {
        return $this->name;
    }

    #[Groups(EnumApiConfig::GET)]
    public function getValue(): int|string
    {
        return $this->value;
    }
}
