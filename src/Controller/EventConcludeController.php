<?php

namespace App\Controller;

use App\Entity\Event;
use App\Message\EventConcludedNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
class EventConcludeController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    )
    {
    }

    public function __invoke(Event $event): Event
    {
        $event->setOver(true);

        $this->messageBus->dispatch(new EventConcludedNotification(
            $event,
        ));

        return $event;
    }
}
