<?php

namespace App\Message;

use App\Entity\Event;
use Symfony\Component\Uid\Uuid;

readonly class EventConcludedNotification
{
    private Uuid $eventId;

    public function __construct(
        Event $event,
    )
    {
        $this->eventId = $event->getId();
    }

    public function getEventId(): Uuid
    {
        return $this->eventId;
    }
}
