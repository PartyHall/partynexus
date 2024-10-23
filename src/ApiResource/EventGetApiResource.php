<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Event;
use App\Entity\Export;
use App\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    shortName: 'event',
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => [
                    Event::API_GET_COLLECTION,
                ],
            ]
        ),
        new Get(
            normalizationContext: [
                'groups' => [
                    Event::API_GET_COLLECTION,
                ],
            ]
        ),
    ],
)]
class EventGetApiResource
{
    #[Groups([
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    public Uuid $id;

    #[Groups([
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    public string $name;

    #[Groups([
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    public ?string $author = null;

    #[Groups([
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    public \DateTimeImmutable $datetime;

    #[Groups([
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    public ?string $location = null;

    #[Groups([
        Event::API_GET_ITEM,
    ])]
    public User $owner;

    #[Groups([
        Event::API_GET_COLLECTION,
        Event::API_GET_ITEM,
    ])]
    public bool $isOver = false;

    #[Groups([Event::API_GET_ITEM])]
    public ?Export $lastExport = null;

    /* @TODO: Timelapse */
}
