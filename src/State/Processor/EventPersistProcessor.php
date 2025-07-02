<?php

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Appliance;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<\App\Entity\Event, \App\Entity\Event>
 */
readonly class EventPersistProcessor implements ProcessorInterface
{
    public function __construct(
        /** @var ProcessorInterface<object, object> $processor */
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
        private Security           $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Event) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $user = $this->security->getUser();
        $owner = $user instanceof User ? $user : ($user instanceof Appliance ? $user->getOwner() : null);

        if ($operation instanceof Post) {
            if (!$owner) {
                throw new BadRequestHttpException('Event can only be created by user or appliance');
            }

            $data->setOwner($owner);
        }

        if ($data->getParticipants()->contains($owner)) {
            $data->setParticipants(
                \array_filter(
                    $data->getParticipants()->toArray(),
                    fn(User $participant) => $participant->getId() !== $owner->getId(),
                ),
            );
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
