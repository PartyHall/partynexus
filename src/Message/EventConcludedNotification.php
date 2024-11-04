<?php

namespace App\Message;

use App\Entity\Event;
use Symfony\Component\Uid\Uuid;

readonly class EventConcludedNotification
{
    private Uuid $eventId;

    public function __construct(
        Event $event,
        private bool $shouldSendEmail,
    ) {
        $this->eventId = $event->getId();
    }

    public function getEventId(): Uuid
    {
        return $this->eventId;
    }

    public function shouldSendEmail(): bool
    {
        return $this->shouldSendEmail;
    }
}
