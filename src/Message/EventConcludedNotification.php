<?php

namespace App\Message;

use Symfony\Component\Uid\Uuid;

readonly class EventConcludedNotification
{
    public function __construct(
        private Uuid $eventId,
        private bool $shouldSendEmail,
    ) {
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
