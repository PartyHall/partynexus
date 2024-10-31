<?php

namespace App\MessageHandler;

use App\Entity\Event;
use App\Message\EventConcludedNotification;
use App\Repository\EventRepository;
use App\Service\EventExporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class EventConcludedNotificationHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private EventRepository $eventRepository,
        private EventExporter $eventExporter,
    )
    {
    }

    public function __invoke(EventConcludedNotification $notification): void
    {
        /** @var Event|null $event */
        $event = $this->eventRepository->find($notification->getEventId());
        if (!$event) {
            $this->logger->error('Failed to find event', ['event_id' => $notification->getEventId()]);
            return;
        }

        try {
            $this->eventExporter->exportEvent($event);
        } catch (\Exception $e) {
            $this->logger->error('Failed to export event', ['event_id' => $notification->getEventId(), 'error' => $e]);
        }
    }
}
