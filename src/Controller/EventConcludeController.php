<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
class EventConcludeController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function __invoke(Event $event): Event
    {
        $event->setOver(true);



        return $event;
    }
}
