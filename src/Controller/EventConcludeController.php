<?php

namespace App\Controller;

use App\Entity\Event;
use App\Message\EventConcludedNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
class EventConcludeController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $emi,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Event $event): Event
    {
        // @TODO: Thats ugly but meh
        // The issue is that the security on the api doesn't get the event for some reason
        // => Unable to call method "getOwner" of non-object "object".
        if (!$this->security->isGranted('ROLE_ADMIN') && $this->security->getUser() !== $event->getOwner()) {
            throw new AccessDeniedHttpException();
        }

        $wasAlreadyOver = $event->isOver();

        $event->setOver(true);
        $this->emi->persist($event);
        $this->emi->flush();

        $this->messageBus->dispatch(new EventConcludedNotification(
            $event->getId(),
            !$wasAlreadyOver,
        ));

        return $event;
    }
}
