<?php

namespace App\Enum;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

trait EnumApiResourcetrait
{
    #[Groups(EnumApiConfig::GET_GROUP)]
    #[ApiProperty(identifier: true)]
    public function getId(): int|string
    {
        return $this->value;
    }

    #[Groups(EnumApiConfig::GET_GROUP)]
    public function getName(): string
    {
        return $this->name;
    }

    #[Groups(EnumApiConfig::GET_GROUP)]
    public function getValue(): int|string
    {
        return $this->value;
    }
}
