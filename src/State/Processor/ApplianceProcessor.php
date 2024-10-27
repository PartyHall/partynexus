<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Appliance;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
readonly class ApplianceProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private Security           $security,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Appliance && $operation instanceof Post) {
            if (($user = $this->security->getUser()) instanceof User) {
                $data->setOwner($user);
                $data->setApiToken(\bin2hex(\random_bytes(32)));
            } else {
                throw new BadRequestHttpException('Only user can create appliances');
            }
        }

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
