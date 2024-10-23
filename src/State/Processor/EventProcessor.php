<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Appliance;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
readonly class EventProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private Security           $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Event && $operation instanceof Post) {
            $user = $this->security->getUser();

            if ($user instanceof User) {
                $data->setOwner($user);
            } else if($user instanceof Appliance) {
                $data->setOwner($user->getOwner());
            } else {
                throw new BadRequestHttpException('Event can only be created by user or appliance');
            }
        }

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
