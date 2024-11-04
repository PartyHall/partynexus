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

readonly class EventCreationProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $processor,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Event && $operation instanceof Post) {
            $user = $this->security->getUser();

            if ($user instanceof User) {
                $data->setOwner($user);
            } elseif ($user instanceof Appliance) {
                $data->setOwner($user->getOwner());
            } else {
                throw new BadRequestHttpException('Event can only be created by user or appliance');
            }
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
