<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @implements ProcessorInterface<Event, Event>
 */
readonly class SelfJoinEventProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $emi,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof Event) {
            throw new \InvalidArgumentException('You should only use the SelfJoinEventProcessor on an event');
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            // Just to be sure appliances cant fuck up anything
            throw new HttpException(403, 'Only users can join an event');
        }

        if (!$data->isUserRegistrationEnabled()) {
            throw new HttpException(403, 'User registration is not enabled');
        }

        // Owner is already "in" the event
        if ($user->getId() === $data->getOwner()->getId()) {
            return $data;
        }

        $data->addParticipant($user);
        $this->emi->persist($data);
        $this->emi->flush();

        return $data;
    }
}
